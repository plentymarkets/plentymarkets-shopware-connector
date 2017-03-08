<?php

namespace PlentyConnector\Connector\ServiceBus;

use ReflectionClass;

/**
 * Class CommandType
 */
final class CommandType
{
    const HANDLE = 'handle';
    const REMOVE = 'remove';

    /**
     * @return array
     */
    public static function getAllTypes()
    {
        $reflection = new ReflectionClass(__CLASS__);

        return $reflection->getConstants();
    }
}
