<?php

namespace SystemConnector\ServiceBus\CommandHandlerMiddleware\Exception;

use Exception;
use SystemConnector\ServiceBus\Command\CommandInterface;

class NotFoundException extends Exception
{
    /**
     * @param CommandInterface $command
     *
     * @return self
     */
    public static function fromCommand(CommandInterface $command): self
    {
        $name = substr(strrchr(get_class($command), '\\'), 1);

        $message = 'No matching command handler found: ' . $name;

        return new self($message);
    }
}
