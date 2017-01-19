<?php

namespace PlentyConnector\Connector\MappingService;

use PlentyConnector\Connector\QueryBus\QueryFactory\Exception\MissingQueryException;
use PlentyConnector\Connector\QueryBus\QueryFactory\Exception\MissingQueryGeneratorException;
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
     * @param bool $fresh
     *
     * @return MappingInterface[]
     *
     * @throws MissingQueryException
     * @throws MissingQueryGeneratorException
     */
    public function getMappingInformation($objectType = null, $fresh = false);
}
