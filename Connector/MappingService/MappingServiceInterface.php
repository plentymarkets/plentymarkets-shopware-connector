<?php

namespace PlentyConnector\Connector\MappingService;

use PlentyConnector\Connector\ServiceBus\QueryFactory\Exception\MissingQueryException;
use PlentyConnector\Connector\ServiceBus\QueryFactory\Exception\MissingQueryGeneratorException;
use PlentyConnector\Connector\ValueObject\Definition\DefinitionInterface;
use PlentyConnector\Connector\ValueObject\Mapping\MappingInterface;

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
     * @param null $objectType
     *
     * @throws MissingQueryException
     * @throws MissingQueryGeneratorException
     *
     * @return MappingInterface[]
     */
    public function getMappingInformation($objectType = null);
}
