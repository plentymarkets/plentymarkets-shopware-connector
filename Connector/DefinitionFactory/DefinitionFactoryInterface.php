<?php

namespace SystemConnector\DefinitionFactory;

use SystemConnector\DefinitionProvider\Struct\Definition;

interface DefinitionFactoryInterface
{
    /**
     * @param $originAdapterName
     * @param $destinationAdapterName
     * @param $objectType
     * @param null $priority
     */
    public function factory($originAdapterName, $destinationAdapterName, $objectType, $priority = null): Definition;
}
