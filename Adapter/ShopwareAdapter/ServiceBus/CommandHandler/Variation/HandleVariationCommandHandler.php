<?php

namespace ShopwareAdapter\ServiceBus\CommandHandler\Variation;

use Doctrine\ORM\EntityManagerInterface;
use PlentyConnector\Connector\IdentityService\IdentityServiceInterface;
use PlentyConnector\Connector\ServiceBus\Command\CommandInterface;
use PlentyConnector\Connector\ServiceBus\Command\TransferObjectCommand;
use PlentyConnector\Connector\ServiceBus\CommandHandler\CommandHandlerInterface;
use PlentyConnector\Connector\ServiceBus\CommandType;
use PlentyConnector\Connector\TransferObject\Product\Product;
use PlentyConnector\Connector\TransferObject\Product\Variation\Variation;
use PlentyConnector\Connector\ValueObject\Identity\Identity;
use Psr\Log\LoggerInterface;
use Shopware\Components\Api\Manager;
use Shopware\Components\Api\Resource\Variant;
use Shopware\Models\Article\Detail;
use ShopwareAdapter\DataPersister\Attribute\AttributeDataPersisterInterface;
use ShopwareAdapter\RequestGenerator\Product\Variation\VariationRequestGeneratorInterface;
use ShopwareAdapter\ShopwareAdapter;

class HandleVariationCommandHandler implements CommandHandlerInterface
{
    /**
     * @var IdentityServiceInterface
     */
    private $identityService;

    /**
     * @var VariationRequestGeneratorInterface
     */
    private $variationRequestGenerator;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var AttributeDataPersisterInterface
     */
    private $attributeDataPersister;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        IdentityServiceInterface $identityService,
        VariationRequestGeneratorInterface $variationRequestGenerator,
        EntityManagerInterface $entityManager,
        AttributeDataPersisterInterface $attributeDataPersister,
        LoggerInterface $logger
    ) {
        $this->identityService = $identityService;
        $this->variationRequestGenerator = $variationRequestGenerator;
        $this->entityManager = $entityManager;
        $this->attributeDataPersister = $attributeDataPersister;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(CommandInterface $command)
    {
        return $command instanceof TransferObjectCommand &&
            $command->getAdapterName() === ShopwareAdapter::NAME &&
            $command->getObjectType() === Variation::TYPE &&
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
         * @var Variation $variation
         */
        $variation = $command->getPayload();

        $productIdentitiy = $this->identityService->findOneBy([
            'objectIdentifier' => $variation->getProductIdentifier(),
            'objectType' => Product::TYPE,
            'adapterName' => ShopwareAdapter::NAME,
        ]);

        if (null === $productIdentitiy) {
            return false;
        }

        $variationIdentity = $this->identityService->findOneBy([
            'objectIdentifier' => $variation->getIdentifier(),
            'objectType' => Variation::TYPE,
            'adapterName' => ShopwareAdapter::NAME,
        ]);

        $variationResource = $this->getVariationResource();
        $variationParams = $this->variationRequestGenerator->generate($variation);
        $variantRepository = $this->entityManager->getRepository(Detail::class);

        /**
         * @var null|Detail $variationModel
         */
        $variationModel = $variantRepository->findOneBy(['number' => $variation->getNumber()]);

        $this->handleVariationProductMigration($variationModel, $productIdentitiy, $variation);

        if (null === $variationIdentity) {
            if (null === $variationModel) {
                $variationModel = $variationResource->create($variationParams);
            } else {
                $variationModel = $variationResource->update(
                    $variationModel->getId(),
                    $variationParams
                );
            }

            $this->identityService->create(
                $variation->getIdentifier(),
                Variation::TYPE,
                (string) $variationModel->getId(),
                ShopwareAdapter::NAME
            );
        } else {
            if (null === $variationModel) {
                $variationModel = $variationResource->create($variationParams);
            } else {
                // remove orphaned and now duplicate variation in case of number changes
                if ($variationModel->getId() !== (int) $variationIdentity->getAdapterIdentifier()) {
                    $this->entityManager->remove($variationModel);
                    $this->entityManager->flush();
                }

                $variationModel = $variationResource->update(
                    $variationIdentity->getAdapterIdentifier(),
                    $variationParams
                );
            }
        }

        $this->correctMainDetailAssignment($variationModel, $variation);

        $this->attributeDataPersister->saveProductDetailAttributes(
            $variationModel,
            $variation->getAttributes()
        );

        return true;
    }

    /**
     * @param Detail    $variant
     * @param Variation $variation
     */
    private function correctMainDetailAssignment(Detail $variant, Variation $variation)
    {
        if (!$variation->isMain()) {
            return;
        }

        $this->entityManager->getConnection()->update(
            's_articles',
            ['main_detail_id' => $variant->getId()],
            ['id' => $variant->getArticle()->getId()]
        );
    }

    /**
     * @return Variant
     */
    private function getVariationResource()
    {
        // without this reset the entitymanager sometimes the album is not found correctly.
        Shopware()->Container()->reset('models');

        return Manager::getResource('Variant');
    }

    /**
     * migrating variation from one product to the correct connector handeled product
     *
     * @param Detail|null $variationModel
     * @param Identity    $productIdentitiy
     * @param Variation   $variation
     */
    private function handleVariationProductMigration($variationModel, $productIdentitiy, $variation)
    {
        if (null === $variationModel) {
            return;
        }

        if ((int) $productIdentitiy->getAdapterIdentifier() === $variationModel->getArticleId()) {
            return;
        }

        $this->entityManager->getConnection()->update(
            's_articles_details',
            ['articleID' => $productIdentitiy->getAdapterIdentifier()],
            ['id' => $variationModel->getId()]
        );

        $this->logger->notice('migrated variation from existing product to connector handeled product.', [
            'variation' => $variation->getNumber(),
            'old product id' => $variationModel->getArticleId(),
            'new product id' => $productIdentitiy->getAdapterIdentifier(),
        ]);
    }
}
