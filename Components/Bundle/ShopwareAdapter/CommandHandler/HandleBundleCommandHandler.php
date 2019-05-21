<?php

namespace PlentyConnector\Components\Bundle\ShopwareAdapter\CommandHandler;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Exception;
use PlentyConnector\Components\Bundle\Helper\BundleHelper;
use PlentyConnector\Components\Bundle\TransferObject\Bundle;
use Psr\Log\LoggerInterface;
use Shopware\Models\Article\Article;
use Shopware\Models\Article\Detail;
use Shopware\Models\Customer\Group;
use ShopwareAdapter\ShopwareAdapter;
use SwagBundle\Models\Article as BundleItems;
use SwagBundle\Models\Bundle as SwagBundle;
use SwagBundle\Models\Price as PriceModel;
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
     * @var EntityRepository
     */
    private $detailRepository;

    /**
     * @var EntityRepository
     */
    private $bundleRepository;

    /**
     * @var BundleHelper
     */
    private $bundleHelper;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * HandleBundleCommandHandler constructor.
     * @param IdentityServiceInterface $identityService
     * @param EntityManagerInterface $entityManager
     * @param BundleHelper $bundleHelper
     * @param LoggerInterface $logger
     */
    public function __construct(
        IdentityServiceInterface $identityService,
        EntityManagerInterface $entityManager,
        BundleHelper $bundleHelper,
        LoggerInterface $logger
    ) {
        $this->identityService = $identityService;
        $this->entityManager = $entityManager;
        $this->detailRepository = $entityManager->getRepository(Detail::class);
        $this->bundleRepository = $entityManager->getRepository(SwagBundle::class);
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

        if (null === $identity) {
            /**
             * @var SwagBundle $existingBundle
             */
            $existingBundle = $this->bundleRepository->findOneBy(['number' => $bundle->getNumber()]);

            if (null !== $existingBundle) {
                $identity = $this->identityService->insert(
                    $bundle->getIdentifier(),
                    Bundle::TYPE,
                    (string) $existingBundle->getId(),
                    ShopwareAdapter::NAME
                );
            }
        } else {
            /**
             * @var SwagBundle $existingBundle
             */
            $existingBundle = $this->bundleRepository->find($identity->getAdapterIdentifier());

            if (null === $existingBundle) {
                $this->identityService->remove($identity);

                $identity = null;
            }
        }

        if (null === $identity) {
            $swagBundle = new SwagBundle();
            $swagBundle->setDisplayGlobal(true);
            $swagBundle->setSells(0);
            $swagBundle->setCreated();
            $swagBundle->setType(1);
            $swagBundle->setDiscountType('abs');
            $swagBundle->setQuantity(0);
            $swagBundle->setShowName(false);
            $swagBundle->setDisplayDelivery(2);
        } else {
            /**
             * @var SwagBundle $swagBundle
             */
            $swagBundle = $this->bundleRepository->find($identity->getAdapterIdentifier());

            foreach ($swagBundle->getPrices() as $price) {
                $this->entityManager->remove($price);
            }

            foreach ($swagBundle->getArticles() as $article) {
                $this->entityManager->remove($article);
            }

            $this->entityManager->flush();
        }

        try {
            /**
             * @var Detail $mainVariant
             */
            $mainVariant = $this->getMainVariant($bundle);
        } catch (Exception $exception) {
            $this->logger->error($exception->getMessage());

            return false;
        }

        if (null === $mainVariant) {
            return false;
        }

        /**
         * @var Article $mainArticle
         */
        $mainArticle = $mainVariant->getArticle();

        $swagBundle->setName($bundle->getName());
        $swagBundle->setValidFrom($bundle->getAvailableFrom());
        $swagBundle->setValidTo($bundle->getAvailableTo());
        $swagBundle->setLimited($bundle->hasStockLimitation());
        $swagBundle->setQuantity($this->getBundleStock($bundle->getNumber()));
        $swagBundle->setNumber($bundle->getNumber());
        $swagBundle->setArticle($mainArticle);
        $swagBundle->setCustomerGroups($this->getCustomerGroups($bundle));
        $swagBundle->setPrices($this->getPrices($bundle, $swagBundle));
        $swagBundle->setPosition($bundle->getPosition());
        $swagBundle->setArticles($this->getArticles($bundle, $swagBundle, $mainVariant));
        $swagBundle->setActive($mainArticle->getActive());
        $swagBundle->setLimitedDetails([$mainVariant]);

        $this->entityManager->persist($swagBundle);
        $this->entityManager->flush();

        return true;
    }

    /**
     * @param Price $price
     *
     * @return null|Group
     */
    private function getCustomerGroupFromPrice(Price $price)
    {
        $repository = $this->entityManager->getRepository(Group::class);

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
     * @param Bundle     $bundle
     * @param SwagBundle $bundleModel
     *
     * @return ArrayCollection
     */
    private function getPrices(Bundle $bundle, SwagBundle $bundleModel)
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
     * @param Bundle     $bundle
     * @param SwagBundle $bundleModel
     * @param Detail     $mainVariant
     *
     * @return ArrayCollection
     */
    private function getArticles(Bundle $bundle, SwagBundle $bundleModel, Detail $mainVariant)
    {
        $result = [];
        foreach ($bundle->getBundleProducts() as $bundleProduct) {
            if ($mainVariant->getNumber() === $bundleProduct->getNumber()) {
                continue;
            }

            /**
             * @var Detail $detail
             */
            $detail = $this->detailRepository->findOneBy(['number' => $bundleProduct->getNumber()]);

            if (null === $detail) {
                $this->logger->error('bundle product not found => number: ' . $bundleProduct->getNumber());
                $this->active = false;

                continue;
            }

            /**
             * @var BundleItems $product
             */
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
     * @return null|Detail
     */
    private function getMainVariant(Bundle $bundle)
    {
        /**
         * @var Detail $detail
         */
        foreach ($bundle->getBundleProducts() as $bundleProduct) {
            $detail = $this->detailRepository->findOneBy(['number' => $bundleProduct->getNumber()]);

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
        /**
         * @var Detail $detail
         */
        $detail = $this->detailRepository->findOneBy(['number' => $bundleNumber]);

        if (null === $detail) {
            return 0;
        }

        return $detail->getInStock();
    }
}
