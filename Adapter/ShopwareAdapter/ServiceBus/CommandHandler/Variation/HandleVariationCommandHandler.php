<?php

namespace ShopwareAdapter\ServiceBus\CommandHandler\Variation;

use Doctrine\ORM\EntityManagerInterface;
use PlentyConnector\Connector\IdentityService\IdentityServiceInterface;
use PlentyConnector\Connector\ServiceBus\Command\CommandInterface;
use PlentyConnector\Connector\ServiceBus\Command\HandleCommandInterface;
use PlentyConnector\Connector\ServiceBus\Command\Variation\HandleVariationCommand;
use PlentyConnector\Connector\ServiceBus\CommandHandler\CommandHandlerInterface;
use PlentyConnector\Connector\TransferObject\Product\Variation\Variation;
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
     * @var Variant
     */
    private $resource;

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
     * @param Variant                            $resource
     * @param EntityManagerInterface             $entityManager
     * @param AttributeDataPersisterInterface    $attributeDataPersister
     */
    public function __construct(
        IdentityServiceInterface $identityService,
        VariationRequestGeneratorInterface $variationRequestGenerator,
        Variant $resource,
        EntityManagerInterface $entityManager,
        AttributeDataPersisterInterface $attributeDataPersister
    ) {
        $this->identityService = $identityService;
        $this->variationRequestGenerator = $variationRequestGenerator;
        $this->resource = $resource;
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

        $variationParams = $this->variationRequestGenerator->generate($variation);
        $variantRepository = $this->entityManager->getRepository(Detail::class);
        $variant = $variantRepository->findOneBy(['number' => $variation->getNumber()]);

        if (null === $variant) {
            $variant = $this->resource->create($variationParams);
        } else {
            $variant = $this->resource->update($variant->getId(), $variationParams);
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

        if ($variation->isMain()) {
            $this->entityManager->getConnection()->update(
                's_articles',
                ['main_detail_id' => $variant->getId()],
                ['id' => $variant->getArticle()->getId()]
            );
        }

        $this->attributeDataPersister->saveProductDetailAttributes(
            $variant,
            $variation->getAttributes()
        );

        return true;
    }
}
