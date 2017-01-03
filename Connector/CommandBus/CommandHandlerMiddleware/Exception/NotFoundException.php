<?php

namespace PlentyConnector\Connector\CommandBus\CommandHandlerMiddleware\Exception;

use Exception;
use PlentyConnector\Connector\CommandBus\Command\CommandInterface;

/**
 * Class NotFoundException.
 */
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

        $message = 'No handler was found for: ' . $name;

        return new self($message);
    }
}
