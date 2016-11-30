<?php

namespace PlentyConnector\Connector\Mapping;

use PlentyConnector\Connector\TransferObject\Definition\DefinitionInterface;
use PlentyConnector\Connector\TransferObject\Mapping\MappingInterface;

/**
 * Interface MappingServiceInterface.
 */
interface MappingServiceInterface
{
    /**
     * @param DefinitionInterface $definition
     */
    public function addDefinition(DefinitionInterface $definition);

    /**
     * @return MappingInterface[]
     */
    public function getMappingInformation();
}
