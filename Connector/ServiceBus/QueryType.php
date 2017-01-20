<?php

namespace PlentyConnector\Connector\ServiceBus;

use ReflectionClass;

/**
 * Class QueryType
 */
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
        $oClass = new ReflectionClass(__CLASS__);

        return $oClass->getConstants();
    }
}
