<?php

namespace SystemConnector\ServiceBus\CommandHandler;

use SystemConnector\ServiceBus\Command\CommandInterface;

interface CommandHandlerInterface
{
    public function supports(CommandInterface $command): bool;

    public function handle(CommandInterface $command): bool;
}
