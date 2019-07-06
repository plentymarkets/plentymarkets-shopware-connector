<?php

namespace ShopwareAdapter\ServiceBus\CommandHandler\Stock;

use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Shopware\Models\Article\Detail;
use ShopwareAdapter\ShopwareAdapter;
use SystemConnector\IdentityService\IdentityServiceInterface;
use SystemConnector\ServiceBus\Command\CommandInterface;
use SystemConnector\ServiceBus\Command\TransferObjectCommand;
use SystemConnector\ServiceBus\CommandHandler\CommandHandlerInterface;
use SystemConnector\ServiceBus\CommandType;
use SystemConnector\TransferObject\Product\Stock\Stock;
use SystemConnector\TransferObject\Product\Variation\Variation;

class HandleStockCommandHandler implements CommandHandlerInterface
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
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        IdentityServiceInterface $identityService,
        EntityManagerInterface $entityManager,
        LoggerInterface $logger
    ) {
        $this->identityService = $identityService;
        $this->entityManager = $entityManager;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(CommandInterface $command): bool
    {
        return $command instanceof TransferObjectCommand &&
            $command->getAdapterName() === ShopwareAdapter::NAME &&
            $command->getObjectType() === Stock::TYPE &&
            $command->getCommandType() === CommandType::HANDLE;
    }

    /**
     * {@inheritdoc}
     *
     * @param TransferObjectCommand $command
     */
    public function handle(CommandInterface $command): bool
    {
        /**
         * @var Stock $stock
         */
        $stock = $command->getPayload();

        $variationIdentity = $this->identityService->findOneBy([
            'objectIdentifier' => $stock->getVariationIdentifier(),
            'objectType' => Variation::TYPE,
            'adapterName' => ShopwareAdapter::NAME,
        ]);

        if (null === $variationIdentity) {
            $this->logger->notice('could not find variation identity - ' . $stock->getVariationIdentifier());

            return false;
        }

        $this->identityService->findOneOrCreate(
            $variationIdentity->getAdapterIdentifier(),
            ShopwareAdapter::NAME,
            Stock::TYPE
        );

        $variationRepository = $this->entityManager->getRepository(Detail::class);

        /**
         * @var null|Detail $variation
         */
        $variation = $variationRepository->find($variationIdentity->getAdapterIdentifier());

        if (null === $variation) {
            $this->logger->notice('could not find variation - ' . $stock->getVariationIdentifier());

            return false;
        }

        $variation->setInStock($stock->getStock());

        $this->entityManager->persist($variation);
        $this->entityManager->flush();
        $this->entityManager->clear();

        return true;
    }
}
