<?php

namespace SystemConnector\DefinitionFactory;

use SystemConnector\ValueObject\ValueObjectInterface;

interface DefinitionFactoryInterface
{
    /**
     * @param string   $originAdapterName
     * @param string   $destinationAdapterName
     * @param string   $objectType
     * @param null|int $priority
     *
     * @return ValueObjectInterface
     */
    public function factory($originAdapterName, $destinationAdapterName, $objectType, $priority = null);
}
