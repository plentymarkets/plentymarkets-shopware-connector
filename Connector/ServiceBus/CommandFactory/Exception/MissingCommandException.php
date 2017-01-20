<?php

namespace PlentyConnector\Connector\ServiceBus\CommandFactory\Exception;

use InvalidArgumentException;

/**
 * Class MissingCommandException
 */
class MissingCommandException extends InvalidArgumentException
{
    /**
     * @param string $objectType
     * @param string $commandType
     *
     * @return self
     */
    public static function fromObjectData($objectType, $commandType)
    {
        $message = 'No matching command found! type: ' . $objectType . ' queryType: ' . $commandType;

        return new static($message);
    }
}
