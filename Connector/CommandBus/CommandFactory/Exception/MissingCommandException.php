<?php

namespace PlentyConnector\Connector\CommandBus\CommandFactory\Exception;

use InvalidArgumentException;
use PlentyConnector\Connector\TransferObject\Definition\DefinitionInterface;

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
