<?php

namespace ShopwareAdapter\ServiceBus\CommandHandler\Order;

use Doctrine\ORM\EntityManagerInterface;
use PlentyConnector\Connector\IdentityService\IdentityServiceInterface;
use PlentyConnector\Connector\ServiceBus\Command\CommandInterface;
use PlentyConnector\Connector\ServiceBus\Command\Order\RemoveOrderCommand;
use PlentyConnector\Connector\ServiceBus\CommandHandler\CommandHandlerInterface;
use ShopwareAdapter\ShopwareAdapter;

/**
 * Class RemoveOrderCommandHandler.
 */
class RemoveOrderCommandHandler implements CommandHandlerInterface
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var IdentityServiceInterface
     */
    private $identityService;

    /**
     * HandleOrderCommandHandler constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param IdentityServiceInterface $identityService
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        IdentityServiceInterface $identityService
    ) {
        $this->entityManager = $entityManager;
        $this->identityService = $identityService;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(CommandInterface $command)
    {
        return $command instanceof RemoveOrderCommand &&
            $command->getAdapterName() === ShopwareAdapter::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(CommandInterface $command)
    {
        return false;
    }
}
