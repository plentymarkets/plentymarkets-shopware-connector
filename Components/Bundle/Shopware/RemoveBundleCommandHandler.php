<?php

namespace PlentyConnector\Components\Bundle\Shopware;

use PlentyConnector\Components\Bundle\Command\RemoveBundleCommand;
use PlentyConnector\Connector\IdentityService\IdentityServiceInterface;
use PlentyConnector\Connector\ServiceBus\Command\CommandInterface;
use PlentyConnector\Connector\ServiceBus\CommandHandler\CommandHandlerInterface;
use ShopwareAdapter\ShopwareAdapter;

/**
 * Class RemoveBundleCommandHandler.
 */
class RemoveBundleCommandHandler implements CommandHandlerInterface
{
    /**
     * @var IdentityServiceInterface
     */
    private $identityService;

    /**
     * RemoveBundleCommandHandler constructor.
     *
     * @param IdentityServiceInterface $identityService
     */
    public function __construct(IdentityServiceInterface $identityService)
    {
        $this->identityService = $identityService;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(CommandInterface $command)
    {
        return $command instanceof RemoveBundleCommand &&
            $command->getAdapterName() === ShopwareAdapter::NAME;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Exception
     */
    public function handle(CommandInterface $command)
    {
        /**
         * @var RemoveBundleCommand $command
         */
        $identifier = $command->getObjectIdentifier();

        return true;
    }
}
