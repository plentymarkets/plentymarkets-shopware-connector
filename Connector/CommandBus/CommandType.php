<?php

namespace PlentyConnector\Connector\CommandBus;

/**
 * Class CommandType
 */
final class CommandType
{
    const HANDLE = 1;
    const REMOVE = 2;

    /**
     * @return array
     */
    public static function getAllTypes()
    {
        $oClass = new \ReflectionClass(__CLASS__);

        return $oClass->getConstants();
    }
}
