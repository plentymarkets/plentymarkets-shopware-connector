<?php

namespace SystemConnector\ServiceBus\CommandHandler;

use SystemConnector\ServiceBus\Command\CommandInterface;

interface CommandHandlerInterface
{
    /**
     * @param CommandInterface $command
     *
     * @return bool
     */
    public function supports(CommandInterface $command) :bool;

    /**
     * @param CommandInterface $command
     *
     * @return bool
     */
    public function handle(CommandInterface $command) :bool;
}
