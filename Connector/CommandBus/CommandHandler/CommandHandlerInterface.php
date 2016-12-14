<?php

namespace PlentyConnector\Connector\CommandBus\CommandHandler;

use PlentyConnector\Connector\CommandBus\Command\CommandInterface;

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
