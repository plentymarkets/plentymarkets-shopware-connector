<?php

namespace PlentyConnector\Connector\ServiceBus\CommandFactory\Exception;

use InvalidArgumentException;

/**
 * Class MissingCommandGeneratorException
 */
class MissingCommandGeneratorException extends InvalidArgumentException
{
    /**
     * @param string $objectType
     * @param string $commandType
     *
     * @return MissingCommandGeneratorException
     */
    public static function fromObjectData($objectType, $commandType)
    {
        $message = 'No matching command generator found! type: ' . $objectType . ' queryType: ' . $commandType;

        return new self($message);
    }
}
