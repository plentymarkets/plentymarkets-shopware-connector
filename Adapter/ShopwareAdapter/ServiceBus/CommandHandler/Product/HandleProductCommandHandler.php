<?php

namespace ShopwareAdapter\ServiceBus\CommandHandler\Product;

use Doctrine\ORM\EntityManagerInterface;
use Shopware\Components\Api\Exception\NotFoundException;
use Shopware\Components\Api\Manager;
use Shopware\Components\Api\Resource\Article;
use Shopware\Components\Api\Resource\Variant;
use Shopware\Models\Article\Article as ArticleModel;
use Shopware\Models\Article\Detail;
use Shopware\Models\Article\Image;
use ShopwareAdapter\DataPersister\Attribute\AttributeDataPersisterInterface;
use ShopwareAdapter\DataPersister\Translation\TranslationDataPersisterInterface;
use ShopwareAdapter\DataProvider\Shop\ShopDataProviderInterface;
use ShopwareAdapter\RequestGenerator\Product\ProductRequestGeneratorInterface;
use ShopwareAdapter\ShopwareAdapter;
use SystemConnector\IdentityService\IdentityServiceInterface;
use SystemConnector\ServiceBus\Command\CommandInterface;
use SystemConnector\ServiceBus\Command\TransferObjectCommand;
use SystemConnector\ServiceBus\CommandHandler\CommandHandlerInterface;
use SystemConnector\ServiceBus\CommandType;
use SystemConnector\TransferObject\Language\Language;
use SystemConnector\TransferObject\Product\Product;
use SystemConnector\Translation\TranslationHelperInterface;

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

        $articleResource = $this->getArticleResource();

        $productIdentity = $this->identityService->findOneBy([
            'objectIdentifier' => $product->getIdentifier(),
            'objectType' => Product::TYPE,
            'adapterName' => ShopwareAdapter::NAME,
        ]);

        $variationRepository = $this->entityManager->getRepository(Detail::class);

        /**
         * @var null|Detail $mainVariation
         */
        $mainVariation = $variationRepository->findOneBy(['number' => $product->getNumber()]);

        if (null === $productIdentity) {
            if (null === $mainVariation) {
                $productModel = $articleResource->create($params);
            } else {
                $this->correctMainDetailAssignment($mainVariation);


                foreach ($mainVariation->getImages() as $image) {
                    if (null !== $image) {
                        $this->translationDataPersister->removeMediaTranslation($image);
                    }
                }

                $productModel = $articleResource->update($mainVariation->getArticleId(), $params);
            }

            $this->identityService->insert(
                $product->getIdentifier(),
                Product::TYPE,
                (string) $productModel->getId(),
                ShopwareAdapter::NAME
            );
        } else {
            try {
                $articleRepository = $this->entityManager->getRepository(ArticleModel::class);

                $productModel = $articleRepository->find($productIdentity->getAdapterIdentifier());

                if (null === $mainVariation) {
                    $variationResource = $this->getVariationResource();

                    $mainVariation = $variationResource->create([
                        'articleId' => $productIdentity->getAdapterIdentifier(),
                        'number' => $product->getNumber(),
                        'active' => true,
                    ]);
                }

                foreach ($productModel->getImages() as $image) {
                    if (null !== $image) {
                        $this->translationDataPersister->removeMediaTranslation($image);
                    }
                }

                $this->correctMainDetailAssignment($mainVariation);
                $productModel = $articleResource->update($productModel->getId(), $params);
            } catch (NotFoundException $exception) {
                $productModel = $articleResource->create($params);

                $this->identityService->update(
                    $productIdentity,
                    [
                        'adapterIdentifier' => (string) $productModel->getId(),
                    ]
                );
            }

            $this->attributeDataPersister->saveProductDetailAttributes(
                $productModel->getMainDetail(),
                $product->getAttributes()
            );

            $this->translationDataPersister->writeProductTranslations($product);
        }

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
     * @return Variant
     */
    private function getVariationResource()
    {
        return Manager::getResource('Variant');
    }
}
