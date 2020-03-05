<?php

namespace SystemConnector\ServiceBus;

use ReflectionClass;

final class CommandType
{
    const HANDLE = 'handle';
    const REMOVE = 'remove';

    public static function getAllTypes(): array
    {
        $reflection = new ReflectionClass(__CLASS__);

        return $reflection->getConstants();
    }
}
