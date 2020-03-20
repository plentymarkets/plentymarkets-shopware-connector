<?php

namespace SystemConnector\ServiceBus\QueryFactory\Exception;

use InvalidArgumentException;

class MissingQueryException extends InvalidArgumentException
{
    /**
     * @param string $objectType
     * @param string $queryType
     */
    public static function fromObjectData($objectType, $queryType): MissingQueryException
    {
        $message = 'No matching query found! type: ' . $objectType . ' queryType: ' . $queryType;

        return new self($message);
    }
}
