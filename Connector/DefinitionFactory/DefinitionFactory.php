<?php

namespace PlentyConnector\Connector\DefinitionFactory;

use PlentyConnector\Connector\ValueObject\Definition\Definition;
use PlentyConnector\Connector\ValueObject\ValueObjectInterface;

/**
 * Class DefinitionFactory
 */
class DefinitionFactory
{
    /**
     * @param string $originAdapterName
     * @param string $destinationAdapterName
     * @param strin $objectType
     * @param null|int $priority
     *
     * @return ValueObjectInterface
     */
    public function factory($originAdapterName, $destinationAdapterName, $objectType, $priority = null)
    {
        return Definition::fromArray([
            'originAdapterName' => $originAdapterName,
            'destinationAdapterName' => $destinationAdapterName,
            'objectType' => $objectType,
            'priority' => $priority,
        ]);
    }
}
