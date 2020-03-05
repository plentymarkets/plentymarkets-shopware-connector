<?php

namespace SystemConnector\ServiceBus;

use ReflectionClass;

final class QueryType
{
    const ALL = 'all';
    const CHANGED = 'changed';
    const ONE = 'one';

    public static function getAllTypes(): array
    {
        $reflection = new ReflectionClass(__CLASS__);

        return $reflection->getConstants();
    }
}
