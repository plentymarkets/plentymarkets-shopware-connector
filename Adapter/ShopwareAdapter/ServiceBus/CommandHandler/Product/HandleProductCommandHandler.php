<?php

namespace ShopwareAdapter\ServiceBus\CommandHandler\Product;

use Doctrine\ORM\EntityManagerInterface;
use PlentyConnector\Connector\IdentityService\IdentityServiceInterface;
use PlentyConnector\Connector\ServiceBus\Command\CommandInterface;
use PlentyConnector\Connector\ServiceBus\Command\TransferObjectCommand;
use PlentyConnector\Connector\ServiceBus\CommandHandler\CommandHandlerInterface;
use PlentyConnector\Connector\ServiceBus\CommandType;
use PlentyConnector\Connector\TransferObject\Language\Language;
use PlentyConnector\Connector\TransferObject\Product\Product;
use PlentyConnector\Connector\Translation\TranslationHelperInterface;
use Shopware\Components\Api\Manager;
use Shopware\Components\Api\Resource\Article;
use Shopware\Models\Article\Detail;
use Shopware\Models\Article\Repository;
use ShopwareAdapter\DataPersister\Attribute\AttributeDataPersisterInterface;
use ShopwareAdapter\DataPersister\Translation\TranslationDataPersisterInterface;
use ShopwareAdapter\DataProvider\Shop\ShopDataProviderInterface;
use ShopwareAdapter\RequestGenerator\Product\ProductRequestGeneratorInterface;
use ShopwareAdapter\ShopwareAdapter;

class HandleProductCommandHandler implements CommandHandlerInterface
{
    /**
     * @var IdentityServiceInterface
     */
    private $identityService;

    /**
     * @var TranslationHelperInterface
     */
    private $translationHelper;

    /**
     * @var AttributeDataPersisterInterface
     */
    private $attributeDataPersister;

    /**
     * @var ProductRequestGeneratorInterface
     */
    private $productRequestGenerator;

    /**
     * @var TranslationDataPersisterInterface
     */
    private $translationDataPersister;

    /**
     * @var ShopDataProviderInterface
     */
    private $shopDataProvider;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(
        EntityManagerInterface $entityManager,
        IdentityServiceInterface $identityService,
        TranslationHelperInterface $translationHelper,
        AttributeDataPersisterInterface $attributeDataPersister,
        ProductRequestGeneratorInterface $productRequestGenerator,
        TranslationDataPersisterInterface $translationDataPersister,
        ShopDataProviderInterface $shopDataProvider
    ) {
        $this->identityService = $identityService;
        $this->translationHelper = $translationHelper;
        $this->attributeDataPersister = $attributeDataPersister;
        $this->productRequestGenerator = $productRequestGenerator;
        $this->translationDataPersister = $translationDataPersister;
        $this->entityManager = $entityManager;
        $this->shopDataProvider = $shopDataProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(CommandInterface $command)
    {
        return $command instanceof TransferObjectCommand &&
            $command->getAdapterName() === ShopwareAdapter::NAME &&
            $command->getObjectType() === Product::TYPE &&
            $command->getCommandType() === CommandType::HANDLE;
    }

    /**
     * {@inheritdoc}
     *
     * @param TransferObjectCommand $command
     */
    public function handle(CommandInterface $command)
    {
        $shopLocaleId = $this->shopDataProvider->getDefaultShop()->getLocale()->getId();

        $languageIdentity = $this->identityService->findOneBy([
            'adapterIdentifier' => (string) $shopLocaleId,
            'adapterName' => ShopwareAdapter::NAME,
            'objectType' => Language::TYPE,
        ]);

        /**
         * @var Product $product
         */
        $product = $command->getPayload();

        if (null !== $languageIdentity) {
            /**
             * @var Product $translated
             */
            $translated = $this->translationHelper->translate($languageIdentity->getObjectIdentifier(), $product);

            if (null !== $translated) {
                $product = $translated;
            }
        }

        $params = $this->productRequestGenerator->generate($product);

        if (empty($params)) {
            return false;
        }

        $resource = $this->getArticleResource();

        $productIdentity = $this->identityService->findOneBy([
            'objectIdentifier' => $product->getIdentifier(),
            'objectType' => Product::TYPE,
            'adapterName' => ShopwareAdapter::NAME,
        ]);

        $variantRepository = $this->entityManager->getRepository(Detail::class);

        /**
         * @var Detail|null $mainVariation
         */
        $mainVariation = $variantRepository->findOneBy(['number' => $product->getNumber()]);

        if (null === $mainVariation) {
            $mainVariation = $this->getExistingVariation($product->getVariantNumbers(), $variantRepository);
        }

        if (null === $productIdentity) {
            if (null === $mainVariation) {
                $productModel = $resource->create($params);
            } else {
                $this->correctMainDetailAssignment($mainVariation);
                $productModel = $resource->update($mainVariation->getArticleId(), $params);
            }

            $this->identityService->create(
                $product->getIdentifier(),
                Product::TYPE,
                (string) $productModel->getId(),
                ShopwareAdapter::NAME
            );
        } elseif (null === $mainVariation) {
            $this->identityService->remove($productIdentity);
            $productModel = $resource->create($params);

            $this->identityService->create(
                $product->getIdentifier(),
                Product::TYPE,
                (string) $productModel->getId(),
                ShopwareAdapter::NAME
            );
        } else {
            $this->correctMainDetailAssignment($mainVariation);
            $productModel = $resource->update($mainVariation->getArticleId(), $params);
        }

        $this->attributeDataPersister->saveProductDetailAttributes(
            $productModel->getMainDetail(),
            $product->getAttributes()
        );

        $this->translationDataPersister->writeProductTranslations($product);

        return true;
    }

    /**
     * @param Detail $mainVariation
     */
    private function correctMainDetailAssignment(Detail $mainVariation)
    {
        $this->entityManager->getConnection()->update(
            's_articles',
            ['main_detail_id' => $mainVariation->getId()],
            ['id' => $mainVariation->getArticle()->getId()]
        );

        $this->entityManager->getConnection()->update(
            's_articles_details',
            ['kind' => 2],
            ['articleID' => $mainVariation->getArticle()->getId()]
        );

        $this->entityManager->getConnection()->update(
            's_articles_details',
            ['kind' => 1],
            ['id' => $mainVariation->getId()]
        );
    }

    /**
     * @return Article
     */
    private function getArticleResource()
    {
        // without this reset the entitymanager will write the models in the wrong order, leading
        // to an s_articles_details.articleID cannot be null exception from the dbal driver.
        Shopware()->Container()->reset('models');

        return Manager::getResource('Article');
    }

    /**
     * @param array      $variantNumbers
     * @param Repository $variantRepository $variantRepository
     *
     * @return null|Detail
     */
    private function getExistingVariation(array $variantNumbers, $variantRepository)
    {
        foreach ($variantNumbers as $variantNumber) {
            /**
             * @var Detail $variant
             */
            $variant = $variantRepository->findOneBy(['number' => $variantNumber]);

            if (null !== $variant) {
                return $variant;
            }
        }

        return null;
    }
}
