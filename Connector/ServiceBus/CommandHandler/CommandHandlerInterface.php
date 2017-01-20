<?php

namespace PlentyConnector\Connector\ServiceBus\CommandHandler;

use PlentyConnector\Connector\ServiceBus\Command\CommandInterface;

/**
 * Interface CommandHandlerInterface.
 */
interface CommandHandlerInterface
{
    /**
     * @param CommandInterface $command
     *
     * @return bool
     */
    public function supports(CommandInterface $command);

    /**
     * @param CommandInterface $command
     */
    public function handle(CommandInterface $command);
}
