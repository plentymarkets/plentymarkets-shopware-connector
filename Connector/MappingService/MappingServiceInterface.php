<?php

namespace PlentyConnector\Connector\MappingService;

use PlentyConnector\Connector\ValueObject\Definition\Definition;
use PlentyConnector\Connector\ValueObject\Mapping\Mapping;

interface MappingServiceInterface
{
    /**
     * @param Definition $definition
     */
    public function addDefinition(Definition $definition);

    /**
     * @param null $objectType
     *
     * @return Mapping[]
     */
    public function getMappingInformation($objectType = null);
}
