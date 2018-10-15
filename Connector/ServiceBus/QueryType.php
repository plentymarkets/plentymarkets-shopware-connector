<?php

namespace SystemConnector\ServiceBus;

use ReflectionClass;

final class QueryType
{
    const ALL = 'all';
    const CHANGED = 'changed';
    const ONE = 'one';

    /**
     * @return array
     */
    public static function getAllTypes()
    {
        $reflection = new ReflectionClass(__CLASS__);

        return $reflection->getConstants();
    }
}
