<?php

namespace SystemConnector\ServiceBus;

use ReflectionClass;

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
