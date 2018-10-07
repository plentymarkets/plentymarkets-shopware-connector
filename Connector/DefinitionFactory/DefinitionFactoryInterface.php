<?php

namespace PlentyConnector\Connector\DefinitionFactory;

use PlentyConnector\Connector\ValueObject\ValueObjectInterface;

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
