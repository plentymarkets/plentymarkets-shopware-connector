<?php

namespace PlentyConnector\Connector\ServiceBus\CommandHandler;

use PlentyConnector\Connector\ServiceBus\Command\CommandInterface;

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
     *
     * @return bool
     */
    public function handle(CommandInterface $command);
}
