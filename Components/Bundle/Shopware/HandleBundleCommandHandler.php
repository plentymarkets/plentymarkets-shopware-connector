<?php

namespace PlentyConnector\Components\Bundle\Shopware;

use PlentyConnector\Components\Bundle\Command\HandleBundleCommand;
use PlentyConnector\Components\Bundle\TransferObject\Bundle;
use PlentyConnector\Connector\IdentityService\IdentityServiceInterface;
use PlentyConnector\Connector\ServiceBus\Command\CommandInterface;
use PlentyConnector\Connector\ServiceBus\CommandHandler\CommandHandlerInterface;
use ShopwareAdapter\ShopwareAdapter;

/**
 * Class HandleBundleCommandHandler.
 */
class HandleBundleCommandHandler implements CommandHandlerInterface
{
    /**
     * @var IdentityServiceInterface
     */
    private $identityService;

    /**
     * HandleBundleCommandHandler constructor.
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
        return $command instanceof HandleBundleCommand &&
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
         * @var HandleBundleCommand $command
         * @var Bundle              $bundle
         */
        $bundle = $command->getTransferObject();

        return true;
    }
}
