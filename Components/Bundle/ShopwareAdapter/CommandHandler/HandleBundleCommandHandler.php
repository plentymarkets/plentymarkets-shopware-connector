<?php

namespace PlentyConnector\Components\Bundle\ShopwareAdapter\CommandHandler;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use PlentyConnector\Components\Bundle\Helper\BundleHelper;
use PlentyConnector\Components\Bundle\TransferObject\Bundle;
use Psr\Log\LoggerInterface;
use Shopware\Models\Article\Detail as DetailModel;
use Shopware\Models\Customer\Group as GroupModel;
use ShopwareAdapter\ShopwareAdapter;
use SwagBundle\Models\Article;
use SwagBundle\Models\Bundle as BundleModel;
use SwagBundle\Models\Price as PriceModel;
use SwagBundle\Models\Repository as BundleRepository;
use SystemConnector\IdentityService\Exception\NotFoundException;
use SystemConnector\IdentityService\IdentityServiceInterface;
use SystemConnector\ServiceBus\Command\CommandInterface;
use SystemConnector\ServiceBus\Command\TransferObjectCommand;
use SystemConnector\ServiceBus\CommandHandler\CommandHandlerInterface;
use SystemConnector\ServiceBus\CommandType;
use SystemConnector\TransferObject\CustomerGroup\CustomerGroup;
use SystemConnector\TransferObject\Product\Price\Price;

class HandleBundleCommandHandler implements CommandHandlerInterface
{
    /**
     * @var bool
     */
    private $active;

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
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        IdentityServiceInterface $identityService,
        EntityManagerInterface $entityManager,
        BundleHelper $bundleHelper,
        LoggerInterface $logger
    ) {
        $this->identityService = $identityService;
        $this->entityManager = $entityManager;
        $this->bundleHelper = $bundleHelper;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(CommandInterface $command)
    {
        return $command instanceof TransferObjectCommand &&
            $command->getAdapterName() === ShopwareAdapter::NAME &&
            $command->getObjectType() === Bundle::TYPE &&
            $command->getCommandType() === CommandType::HANDLE;
    }

    /**
     * {@inheritdoc}
     *
     * @param TransferObjectCommand $command
     */
    public function handle(CommandInterface $command)
    {
        /**
         * @var Bundle $bundle
         */
        $bundle = $command->getPayload();

        $identity = $this->identityService->findOneBy(
            [
                'objectIdentifier' => (string) $bundle->getIdentifier(),
                'objectType' => Bundle::TYPE,
                'adapterName' => ShopwareAdapter::NAME,
            ]
        );

        $this->bundleHelper->registerBundleModels();

        /**
         * @var BundleRepository $repository
         */
        $repository = $this->entityManager->getRepository(BundleModel::class);

        if (null === $identity) {
            $existingBundle = $repository->findOneBy(['number' => $bundle->getNumber()]);

            if (null !== $existingBundle) {
                $identity = $this->identityService->insert(
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
            $bundleModel->setCreated();
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

            $this->entityManager->flush();
        }

        try {
            $mainVariant = $this->getMainVariant($bundle);
        } catch (Exception $exception) {
            $this->logger->error($exception->getMessage());

            return false;
        }

        if (null === $mainVariant) {
            return false;
        }

        $mainArticle = $mainVariant->getArticle();

        $this->active = $mainArticle->getActive();

        $bundleModel->setName($bundle->getName());
        $bundleModel->setValidFrom($bundle->getAvailableFrom());
        $bundleModel->setValidTo($bundle->getAvailableTo());
        $bundleModel->setLimited($bundle->hasStockLimitation());
        $bundleModel->setQuantity($this->getBundleStock($bundle->getNumber()));
        $bundleModel->setNumber($bundle->getNumber());
        $bundleModel->setArticle($mainArticle);
        $bundleModel->setCustomerGroups($this->getCustomerGroups($bundle));
        $bundleModel->setPrices($this->getPrices($bundle, $bundleModel));
        $bundleModel->setPosition($bundle->getPosition());
        $bundleModel->setArticles($this->getArticles($bundle, $bundleModel, $mainVariant));
        $bundleModel->setActive($this->active);

        $this->entityManager->persist($bundleModel);
        $this->entityManager->flush();
        $this->entityManager->clear();

        return true;
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

        $identity = $this->identityService->findOneBy(
            [
                'objectIdentifier' => (string) $price->getCustomerGroupIdentifier(),
                'objectType' => CustomerGroup::TYPE,
                'adapterName' => ShopwareAdapter::NAME,
            ]
        );

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
     * @param Bundle      $bundle
     * @param BundleModel $bundleModel
     *
     * @return ArrayCollection
     */
    private function getPrices(Bundle $bundle, BundleModel $bundleModel)
    {
        $prices = [];
        foreach ($bundle->getPrices() as $price) {
            $group = $this->getCustomerGroupFromPrice($price);

            if (null === $group) {
                continue;
            }

            $netPrice = $price->getPrice() * (100 / ($bundleModel->getArticle()->getTax()->getTax() + 100));

            $priceModel = new PriceModel();
            $priceModel->setBundle($bundleModel);
            $priceModel->setCustomerGroup($group);
            $priceModel->setPrice($netPrice);
            $this->entityManager->persist($priceModel);
            $prices[] = $priceModel;
        }

        return new ArrayCollection($prices);
    }

    /**
     * @param Bundle      $bundle
     * @param BundleModel $bundleModel
     * @param DetailModel $mainVariant
     *
     * @return ArrayCollection
     */
    private function getArticles(Bundle $bundle, BundleModel $bundleModel, DetailModel $mainVariant)
    {
        $repository = $this->entityManager->getRepository(DetailModel::class);

        $result = [];
        foreach ($bundle->getBundleProducts() as $bundleProduct) {
            if ($mainVariant->getNumber() === $bundleProduct->getNumber()) {
                continue;
            }

            $detail = $repository->findOneBy(['number' => $bundleProduct->getNumber()]);

            if (null === $detail) {
                $this->logger->error('bundle product not found => number: ' . $bundleProduct->getNumber());
                $this->active = false;

                continue;
            }

            $product = new Article();
            $product->setQuantity($bundleProduct->getAmount());
            $product->setArticleDetail($detail);
            $product->setPosition($bundleProduct->getPosition());
            $product->setBundle($bundleModel);

            $this->entityManager->persist($product);
            $result[] = $product;
        }

        return new ArrayCollection($result);
    }

    /**
     * @param Bundle $bundle
     *
     * @throws NotFoundException
     *
     * @return null|object
     */
    private function getMainVariant(Bundle $bundle)
    {
        $repository = $this->entityManager->getRepository(DetailModel::class);

        foreach ($bundle->getBundleProducts() as $bundleProduct) {
            $detail = $repository->findOneBy(['number' => $bundleProduct->getNumber()]);

            if (null === $detail) {
                throw new NotFoundException('bundle main product not found');
                continue;
            }

            return $detail;
        }

        return null;
    }

    /**
     * @param string $bundleNumber
     *
     * @return int
     */
    private function getBundleStock($bundleNumber)
    {
        $repository = $this->entityManager->getRepository(DetailModel::class);
        $detail = $repository->findOneBy(['number' => $bundleNumber]);

        if (null === $detail) {
            return 0;
        }

        return $detail->getInStock();
    }
}
