<?php

namespace ShopwareAdapter\ServiceBus\CommandHandler\Variation;

use Doctrine\ORM\EntityManagerInterface;
use PlentyConnector\Connector\IdentityService\IdentityServiceInterface;
use PlentyConnector\Connector\ServiceBus\Command\CommandInterface;
use PlentyConnector\Connector\ServiceBus\Command\HandleCommandInterface;
use PlentyConnector\Connector\ServiceBus\Command\Variation\HandleVariationCommand;
use PlentyConnector\Connector\ServiceBus\CommandHandler\CommandHandlerInterface;
use PlentyConnector\Connector\TransferObject\Product\Product;
use PlentyConnector\Connector\TransferObject\Product\Variation\Variation;
use Shopware\Components\Api\Manager;
use Shopware\Components\Api\Resource\Variant;
use Shopware\Models\Article\Detail;
use ShopwareAdapter\DataPersister\Attribute\AttributeDataPersisterInterface;
use ShopwareAdapter\RequestGenerator\Product\Variation\VariationRequestGeneratorInterface;
use ShopwareAdapter\ShopwareAdapter;

/**
 * Class HandleVariationCommandHandler.
 */
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
     * HandleVariationCommandHandler constructor.
     *
     * @param IdentityServiceInterface           $identityService
     * @param VariationRequestGeneratorInterface $variationRequestGenerator
     * @param EntityManagerInterface             $entityManager
     * @param AttributeDataPersisterInterface    $attributeDataPersister
     */
    public function __construct(
        IdentityServiceInterface $identityService,
        VariationRequestGeneratorInterface $variationRequestGenerator,
        EntityManagerInterface $entityManager,
        AttributeDataPersisterInterface $attributeDataPersister
    ) {
        $this->identityService = $identityService;
        $this->variationRequestGenerator = $variationRequestGenerator;
        $this->entityManager = $entityManager;
        $this->attributeDataPersister = $attributeDataPersister;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(CommandInterface $command)
    {
        return $command instanceof HandleVariationCommand &&
            $command->getAdapterName() === ShopwareAdapter::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(CommandInterface $command)
    {
        /**
         * @var HandleCommandInterface $command
         * @var Variation              $variation
         */
        $variation = $command->getTransferObject();

        $productIdentitiy = $this->identityService->findOneBy([
            'objectIdentifier' => $variation->getProductIdentifier(),
            'objectType' => Product::TYPE,
            'adapterName' => ShopwareAdapter::NAME,
        ]);

        if (null === $productIdentitiy) {
            return false;
        }

        $variationParams = $this->variationRequestGenerator->generate($variation);
        $variantRepository = $this->entityManager->getRepository(Detail::class);
        $variant = $variantRepository->findOneBy(['number' => $variation->getNumber()]);

        $resource = $this->getVariationResource();

        if (null === $variant) {
            $variant = $resource->create($variationParams);
        } else {
            $variant = $resource->update($variant->getId(), $variationParams);
        }

        $identities = $this->identityService->findBy([
            'objectIdentifier' => $variation->getIdentifier(),
            'objectType' => Variation::TYPE,
            'adapterName' => ShopwareAdapter::NAME,
        ]);

        $foundIdentity = false;
        foreach ($identities as $identity) {
            if ($identity->getAdapterIdentifier() === (string) $variant->getId()) {
                $foundIdentity = true;

                continue;
            }

            $this->identityService->remove($identity);
        }

        if (!$foundIdentity) {
            $this->identityService->create(
                $variation->getIdentifier(),
                Variation::TYPE,
                (string) $variant->getId(),
                ShopwareAdapter::NAME
            );
        }

        $this->correctMainDetailAssignment($variant, $variation);

        $this->attributeDataPersister->saveProductDetailAttributes(
            $variant,
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
}
