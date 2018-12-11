<?php

namespace ShopwareAdapter\ServiceBus\CommandHandler\Variation;

use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Shopware\Components\Api\Manager;
use Shopware\Components\Api\Resource\Variant;
use Shopware\Models\Article\Detail;
use ShopwareAdapter\DataPersister\Attribute\AttributeDataPersisterInterface;
use ShopwareAdapter\RequestGenerator\Product\Variation\VariationRequestGeneratorInterface;
use ShopwareAdapter\ShopwareAdapter;
use SystemConnector\IdentityService\IdentityServiceInterface;
use SystemConnector\IdentityService\Struct\Identity;
use SystemConnector\ServiceBus\Command\CommandInterface;
use SystemConnector\ServiceBus\Command\TransferObjectCommand;
use SystemConnector\ServiceBus\CommandHandler\CommandHandlerInterface;
use SystemConnector\ServiceBus\CommandType;
use SystemConnector\TransferObject\Product\Product;
use SystemConnector\TransferObject\Product\Variation\Variation;

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
        $variationRepository = $this->entityManager->getRepository(Detail::class);

        if (null === $variationIdentity) {
            /**
             * @var null|Detail $variationModel
             */
            $variationModel = $variationRepository->findOneBy(['number' => $variation->getNumber()]);

            if (null === $variationModel) {
                $variationModel = $variationResource->create($variationParams);
            } else {
                $variationModel = $variationResource->update(
                    $variationModel->getId(),
                    $variationParams
                );
            }

            $this->identityService->insert(
                $variation->getIdentifier(),
                Variation::TYPE,
                (string) $variationModel->getId(),
                ShopwareAdapter::NAME
            );
        } else {
            /**
             * @var null|Detail $variationModel
             */
            $variationModel = $variationRepository->find($variationIdentity->getAdapterIdentifier());

            if (null === $variationModel) {
                $variationModel = $variationRepository->findOneBy(['number' => $variation->getNumber()]);
            }

            if (null === $variationModel) {
                $variationModel = $variationResource->create($variationParams);
            } else {
                $variationModel = $variationResource->update(
                    $variationModel->getId(),
                    $variationParams
                );
            }

            $this->identityService->update(
                $variationIdentity,
                [
                    'adapterIdentifier' => (string) $variationModel->getId(),
                ]
            );
        }

        $this->correctProductAssignment($variationModel, $productIdentitiy);
        $this->correctMainDetailAssignment($variationModel, $variation);

        $this->attributeDataPersister->saveProductDetailAttributes(
            $variationModel,
            $variation->getAttributes()
        );

        return true;
    }

    /**
     * @param Detail    $variationModel
     * @param Variation $variation
     */
    private function correctMainDetailAssignment(Detail $variationModel, Variation $variation)
    {
        if (!$variation->isMain()) {
            return;
        }

        $this->entityManager->getConnection()->update(
            's_articles',
            [
                'main_detail_id' => $variationModel->getId(),
                'active' => $variation->getActive(),
                'changetime' => (new DateTime('now'))->format('Y-m-d H:i:s'),
            ],
            ['id' => $variationModel->getArticle()->getId()]
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
     * @param null|Detail $variationModel
     * @param Identity    $productIdentitiy
     */
    private function correctProductAssignment($variationModel, $productIdentitiy)
    {
        if (null === $variationModel) {
            return;
        }

        if ((int) $productIdentitiy->getAdapterIdentifier() === $variationModel->getArticle()->getId()) {
            return;
        }

        $this->entityManager->getConnection()->update(
            's_articles_details',
            ['articleID' => $productIdentitiy->getAdapterIdentifier()],
            ['id' => $variationModel->getId()]
        );

        $this->logger->notice('migrated variation from existing product to connector handeled product.', [
            'variation' => $variationModel->getNumber(),
            'old shopware product id' => $variationModel->getArticle()->getId(),
            'new shopware product id' => $productIdentitiy->getAdapterIdentifier(),
        ]);
    }
}
