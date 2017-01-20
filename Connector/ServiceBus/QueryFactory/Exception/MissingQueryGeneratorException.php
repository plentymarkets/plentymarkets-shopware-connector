<?php

namespace PlentyConnector\Connector\ServiceBus\QueryFactory\Exception;

use InvalidArgumentException;

/**
 * Class MissingQueryGeneratorException
 */
class MissingQueryGeneratorException extends InvalidArgumentException
{
    /**
     * @param string $objectType
     * @param string $queryType
     *
     * @return MissingQueryGeneratorException
     */
    public static function fromObjectData($objectType, $queryType)
    {
        $message = 'No matching query generator found! type: ' . $objectType . ' queryType: ' . $queryType;

        return new self($message);
    }
}
