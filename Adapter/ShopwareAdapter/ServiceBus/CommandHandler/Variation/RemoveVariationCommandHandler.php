<?php

namespace ShopwareAdapter\ServiceBus\CommandHandler\Variation;

use PlentyConnector\Connector\ServiceBus\Command\CommandInterface;
use PlentyConnector\Connector\ServiceBus\Command\Variation\RemoveVariationCommand;
use PlentyConnector\Connector\ServiceBus\CommandHandler\CommandHandlerInterface;
use ShopwareAdapter\ShopwareAdapter;

/**
 * Class RemoveVariationCommandHandler.
 */
class RemoveVariationCommandHandler implements CommandHandlerInterface
{
    /**
     * {@inheritdoc}
     */
    public function supports(CommandInterface $command)
    {
        return $command instanceof RemoveVariationCommand &&
            $command->getAdapterName() === ShopwareAdapter::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(CommandInterface $command)
    {
        return true;
    }
}
