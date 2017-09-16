<?php

namespace ShopwareAdapter\ServiceBus\CommandHandler\Stock;

use Doctrine\ORM\EntityManagerInterface;
use PlentyConnector\Connector\IdentityService\IdentityServiceInterface;
use PlentyConnector\Connector\ServiceBus\Command\CommandInterface;
use PlentyConnector\Connector\ServiceBus\Command\HandleCommandInterface;
use PlentyConnector\Connector\ServiceBus\Command\Stock\HandleStockCommand;
use PlentyConnector\Connector\ServiceBus\CommandHandler\CommandHandlerInterface;
use PlentyConnector\Connector\TransferObject\Product\Stock\Stock;
use PlentyConnector\Connector\TransferObject\Product\Variation\Variation;
use Psr\Log\LoggerInterface;
use Shopware\Models\Article\Detail;
use ShopwareAdapter\ShopwareAdapter;

/**
 * Class HandleStockCommandHandler.
 */
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

    /**
     * HandleStockCommandHandler constructor.
     *
     * @param IdentityServiceInterface $identityService
     * @param EntityManagerInterface   $entityManager
     * @param LoggerInterface          $logger
     */
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
    public function supports(CommandInterface $command)
    {
        return $command instanceof HandleStockCommand &&
            $command->getAdapterName() === ShopwareAdapter::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(CommandInterface $command)
    {
        /**
         * @var HandleCommandInterface $command
         * @var Stock                  $stock
         */
        $stock = $command->getTransferObject();

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

        $variationRespository = $this->entityManager->getRepository(Detail::class);
        $variation = $variationRespository->find($variationIdentity->getAdapterIdentifier());

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
