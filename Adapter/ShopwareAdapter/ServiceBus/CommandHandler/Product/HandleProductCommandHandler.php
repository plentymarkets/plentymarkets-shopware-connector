<?php

namespace ShopwareAdapter\ServiceBus\CommandHandler\Product;

use Doctrine\ORM\EntityManagerInterface;
use PlentyConnector\Connector\IdentityService\IdentityServiceInterface;
use PlentyConnector\Connector\ServiceBus\Command\CommandInterface;
use PlentyConnector\Connector\ServiceBus\Command\HandleCommandInterface;
use PlentyConnector\Connector\ServiceBus\Command\Product\HandleProductCommand;
use PlentyConnector\Connector\ServiceBus\CommandHandler\CommandHandlerInterface;
use PlentyConnector\Connector\TransferObject\Product\Product;
use Shopware\Components\Api\Manager;
use Shopware\Components\Api\Resource\Article;
use Shopware\Models\Article\Detail;
use ShopwareAdapter\DataPersister\Attribute\AttributeDataPersisterInterface;
use ShopwareAdapter\DataPersister\Translation\TranslationDataPersisterInterface;
use ShopwareAdapter\Helper\AttributeHelper;
use ShopwareAdapter\RequestGenerator\Product\ProductRequestGeneratorInterface;
use ShopwareAdapter\ShopwareAdapter;

/**
 * Class HandleProductCommandHandler.
 */
class HandleProductCommandHandler implements CommandHandlerInterface
{
    /**
     * @var IdentityServiceInterface
     */
    private $identityService;

    /**
     * @var AttributeHelper
     */
    private $attributeHelper;

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
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * HandleProductCommandHandler constructor.
     *
     * @param IdentityServiceInterface          $identityService
     * @param AttributeHelper                   $attributeHelper
     * @param AttributeDataPersisterInterface   $attributeDataPersister
     * @param ProductRequestGeneratorInterface  $productRequestGenerator
     * @param TranslationDataPersisterInterface $translationDataPersister
     * @param EntityManagerInterface            $entityManager
     */
    public function __construct(
        IdentityServiceInterface $identityService,
        AttributeHelper $attributeHelper,
        AttributeDataPersisterInterface $attributeDataPersister,
        ProductRequestGeneratorInterface $productRequestGenerator,
        TranslationDataPersisterInterface $translationDataPersister,
        EntityManagerInterface $entityManager
    ) {
        $this->identityService = $identityService;
        $this->attributeHelper = $attributeHelper;
        $this->attributeDataPersister = $attributeDataPersister;
        $this->productRequestGenerator = $productRequestGenerator;
        $this->translationDataPersister = $translationDataPersister;
        $this->entityManager = $entityManager;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(CommandInterface $command)
    {
        return $command instanceof HandleProductCommand &&
            $command->getAdapterName() === ShopwareAdapter::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(CommandInterface $command)
    {
        /**
         * @var HandleCommandInterface $command
         * @var Product                $product
         */
        $product = $command->getTransferObject();

        $params = $this->productRequestGenerator->generate($product);
        $variantRepository = $this->entityManager->getRepository(Detail::class);
        $mainVariation = $variantRepository->findOneBy(['number' => $product->getNumber()]);

        $resouce = $this->getArticleResource();

        if (null === $mainVariation) {
            $productModel = $resouce->create($params);
        } else {
            $this->correctMainDetailAssignment($mainVariation);

            $productModel = $resouce->update($mainVariation->getArticleId(), $params);
        }

        $identities = $this->identityService->findBy([
            'objectIdentifier' => $product->getIdentifier(),
            'objectType' => Product::TYPE,
            'adapterName' => ShopwareAdapter::NAME,
        ]);

        $foundIdentity = false;
        foreach ($identities as $identity) {
            if ($identity->getAdapterIdentifier() === (string) $productModel->getId()) {
                $foundIdentity = true;

                continue;
            }

            $this->identityService->remove($identity);
        }

        if (!$foundIdentity) {
            $this->identityService->create(
                $product->getIdentifier(),
                Product::TYPE,
                (string) $productModel->getId(),
                ShopwareAdapter::NAME
            );
        }

        $this->attributeHelper->addFieldAsAttribute($product, 'technicalDescription');

        $this->attributeDataPersister->saveProductDetailAttributes(
            $productModel->getMainDetail(),
            $product->getAttributes()
        );

        $this->translationDataPersister->writeProductTranslations($product);

        return true;
    }

    /**
     * @param Detail    $mainVariation
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
}
