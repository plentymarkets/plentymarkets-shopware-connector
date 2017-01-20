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
        $oClass = new ReflectionClass(__CLASS__);

        return $oClass->getConstants();
    }
}
