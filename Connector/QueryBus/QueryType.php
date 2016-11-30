<?php

namespace PlentyConnector\Connector\QueryBus;

/**
 * Class QueryType
 */
final class QueryType
{
    const ALL = 1;
    const CHANGED = 2;
    const ONE = 3;

    /**
     * @return array
     */
    public static function getAllTypes()
    {
        $oClass = new \ReflectionClass(__CLASS__);

        return $oClass->getConstants();
    }
}
