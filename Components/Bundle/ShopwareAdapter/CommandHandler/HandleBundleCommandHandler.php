<?php

namespace PlentyConnector\Components\Bundle\ShopwareAdapter\CommandHandler;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use PlentyConnector\Components\Bundle\Command\HandleBundleCommand;
use PlentyConnector\Components\Bundle\Helper\BundleHelper;
use PlentyConnector\Components\Bundle\TransferObject\Bundle;
use PlentyConnector\Connector\IdentityService\Exception\NotFoundException;
use PlentyConnector\Connector\IdentityService\IdentityServiceInterface;
use PlentyConnector\Connector\ServiceBus\Command\CommandInterface;
use PlentyConnector\Connector\ServiceBus\CommandHandler\CommandHandlerInterface;
use PlentyConnector\Connector\TransferObject\CustomerGroup\CustomerGroup;
use PlentyConnector\Connector\TransferObject\Product\Price\Price;
use PlentyConnector\Connector\TransferObject\Product\Product;
use Shopware\CustomModels\Bundle\Article;
use Shopware\CustomModels\Bundle\Bundle as BundleModel;
use Shopware\CustomModels\Bundle\Price as PriceModel;
use Shopware\CustomModels\Bundle\Repository as BundleRepository;
use Shopware\Models\Article\Article as ArticleModel;
use Shopware\Models\Article\Detail as DetailModel;
use Shopware\Models\Article\Repository as ArticleRepository;
use Shopware\Models\Customer\Group as GroupModel;
use ShopwareAdapter\ShopwareAdapter;

/**
 * Class HandleBundleCommandHandler.
 */
class HandleBundleCommandHandler implements CommandHandlerInterface
{
    /**
     * @var IdentityServiceInterface
     */
    private $identityService;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var BundleHelper
     */
    private $bundleHelper;

    /**
     * HandleBundleCommandHandler constructor.
     *
     * @param IdentityServiceInterface $identityService
     * @param EntityManagerInterface $entityManager
     * @param BundleHelper $bundleHelper
     */
    public function __construct(
        IdentityServiceInterface $identityService,
        EntityManagerInterface $entityManager,
        BundleHelper $bundleHelper
    ) {
        $this->identityService = $identityService;
        $this->entityManager = $entityManager;
        $this->bundleHelper = $bundleHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(CommandInterface $command)
    {
        return $command instanceof HandleBundleCommand &&
            $command->getAdapterName() === ShopwareAdapter::NAME;
    }

    /**
     * @param Bundle $bundle
     *
     * @return ArticleModel
     *
     * @throws NotFoundException
     */
    private function getArticle(Bundle $bundle)
    {
        /**
         * @var ArticleRepository $repository
         */
        $repository = $this->entityManager->getRepository(ArticleModel::class);

        $productIdentity = $this->identityService->findOneBy([
            'objectIdentifier' => $bundle->getProductIdentifier(),
            'objectType' => Product::TYPE,
            'adapterName' => ShopwareAdapter::NAME,
        ]);

        if (null === $productIdentity) {
            throw new NotFoundException('bundle main product not found');
        }

        /**
         * @var ArticleModel $productModel
         */
        $productModel = $repository->find($productIdentity->getAdapterIdentifier());

        if (null === $productModel) {
            throw new NotFoundException('bundle main product not found');
        }

        return $productModel;
    }

    /**
     * @param Price $price
     *
     * @return null|GroupModel
     */
    private function getCustomerGroupFromPrice(Price $price)
    {
        $repository = $this->entityManager->getRepository(GroupModel::class);

        if (null === $price->getCustomerGroupIdentifier()) {
            return $repository->findOneBy(['key' => 'EK']);
        }

        $identity = $this->identityService->findOneBy([
            'objectIdentifier' => (string) $price->getCustomerGroupIdentifier(),
            'objectType' => CustomerGroup::TYPE,
            'adapterName' => ShopwareAdapter::NAME,
        ]);

        if (null === $identity) {
            return null;
        }

        return $repository->find($identity->getAdapterIdentifier());
    }

    /**
     * @param Bundle $bundle
     *
     * @return ArrayCollection
     */
    private function getCustomerGroups(Bundle $bundle)
    {
        $result = [];
        foreach ($bundle->getPrices() as $price) {
            $group = $this->getCustomerGroupFromPrice($price);

            if (null === $group) {
                continue;
            }

            $result[$group->getKey()] = $group;
        }

        return new ArrayCollection($result);
    }

    /**
     * @param Bundle $bundle
     *
     * @return ArrayCollection
     */
    private function getPrices(Bundle $bundle)
    {
        $prices = [];
        foreach ($bundle->getPrices() as $price) {
            $group = $this->getCustomerGroupFromPrice($price);

            if (null === $group) {
                continue;
            }

            $priceModel = new PriceModel();
            $priceModel->setCustomerGroup($group);
            $priceModel->setPrice($price->getPrice());

            $prices[] = $priceModel;
        }

        return new ArrayCollection($prices);
    }

    /**
     * @param Bundle $bundle
     *
     * @return ArrayCollection
     */
    private function getArticles(Bundle $bundle)
    {
        $repository = $this->entityManager->getRepository(DetailModel::class);

        $result = [];
        foreach ($bundle->getBundleProducts() as $bundleProduct) {
            $detail = $repository->findOneBy(['number' => $bundleProduct->getNumber()]);

            if (null === $detail) {
                continue;
            }

            $product = new Article();
            $product->setQuantity($bundleProduct->getAmount());
            $product->setArticleDetail($detail);
            $product->setPosition($bundleProduct->getPosition());
        }

        return new ArrayCollection($result);
    }

    /**
     * {@inheritdoc}
     */
    public function handle(CommandInterface $command)
    {
        /**
         * @var HandleBundleCommand $command
         * @var Bundle $bundle
         */
        $bundle = $command->getTransferObject();

        $identity = $this->identityService->findOneBy([
            'objectIdentifier' => (string) $bundle->getIdentifier(),
            'objectType' => Bundle::TYPE,
            'adapterName' => ShopwareAdapter::NAME,
        ]);

        $this->bundleHelper->registerBundleModels();

        /**
         * @var BundleRepository $repository
         */
        $repository = $this->entityManager->getRepository(BundleModel::class);

        if (null === $identity) {
            $existingBundle = $repository->findOneBy(['number' => $bundle->getNumber()]);

            if (null !== $existingBundle) {
                $identity = $this->identityService->create(
                    $bundle->getIdentifier(),
                    Bundle::TYPE,
                    (string) $existingBundle->getId(),
                    ShopwareAdapter::NAME
                );
            }
        } else {
            $existingBundle = $repository->find($identity->getAdapterIdentifier());

            if (null === $existingBundle) {
                $this->identityService->remove($identity);

                $identity = null;
            }
        }

        if (null === $identity) {
            $bundleModel = new BundleModel();

            $bundleModel->setDisplayGlobal(true);
            $bundleModel->setSells(0);
            $bundleModel->setCreated('now');
            $bundleModel->setType(1);
            $bundleModel->setDiscountType('abs');
            $bundleModel->setQuantity(0);
            $bundleModel->setShowName(false);
        } else {
            /**
             * @var BundleModel $bundleModel
             */
            $bundleModel = $repository->find($identity->getAdapterIdentifier());

            foreach ($bundleModel->getPrices() as $price) {
                $this->entityManager->remove($price);
            }

            foreach ($bundleModel->getArticles() as $article) {
                $this->entityManager->remove($article);
            }

            foreach ($bundleModel->getCustomerGroups() as $customerGroup) {
                $this->entityManager->remove($customerGroup);
            }
        }

        $bundleModel->setName($bundle->getName());
        $bundleModel->setValidFrom($bundle->getAvailableFrom());
        $bundleModel->setValidTo($bundle->getAvailableTo());
        $bundleModel->setLimited($bundle->hasStockLimitation());
        $bundleModel->setActive($bundle->isActive());
        $bundleModel->setQuantity($bundle->getStock());
        $bundleModel->setNumber($bundle->getNumber());
        $bundleModel->setArticle($this->getArticle($bundle));
        $bundleModel->setCustomerGroups($this->getCustomerGroups($bundle));
        $bundleModel->setPrices($this->getPrices($bundle));
        $bundleModel->setArticles($this->getArticles($bundle));

        $this->entityManager->persist($bundleModel);
        $this->entityManager->flush();

        return true;
    }
}
