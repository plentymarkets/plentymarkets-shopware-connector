<?php

namespace PlentyConnector\Connector\MappingService;

use PlentyConnector\Connector\ServiceBus\QueryFactory\Exception\MissingQueryException;
use PlentyConnector\Connector\ValueObject\Definition\Definition;
use PlentyConnector\Connector\ValueObject\Mapping\Mapping;

/**
 * Interface MappingServiceInterface.
 */
interface MappingServiceInterface
{
    /**
     * @param Definition $definition
     */
    public function addDefinition(Definition $definition);

    /**
     * @param null $objectType
     *
     * @throws MissingQueryException
     *
     * @return Mapping[]
     */
    public function getMappingInformation($objectType = null);
}
