<?php

namespace PlentyConnector\Connector\ServiceBus\CommandHandlerMiddleware\Exception;

use Exception;
use PlentyConnector\Connector\ServiceBus\Command\CommandInterface;

class NotFoundException extends Exception
{
    /**
     * @param CommandInterface $command
     *
     * @return self
     */
    public static function fromCommand(CommandInterface $command)
    {
        $name = substr(strrchr(get_class($command), '\\'), 1);

        $message = 'No matching command handler found: ' . $name;

        return new self($message);
    }
}
