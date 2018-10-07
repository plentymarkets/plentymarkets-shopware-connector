<?php

namespace PlentyConnector\Connector\DefinitionProvider;

use PlentyConnector\Connector\ValueObject\Definition\Definition;

interface DefinitionProviderInterface
{
    /**
     * @param null|string $objectType
     *
     * @return Definition[]
     */
    public function getConnectorDefinitions($objectType = null);

    /**
     * @param Definition $definition
     */
    public function addConnectorDefinition(Definition $definition);

    /**
     * @param null $objectType
     *
     * @return Definition[]
     */
    public function getMappingDefinitions($objectType = null);

    /**
     * @param Definition $definition
     */
    public function addMappingDefinition(Definition $definition);

    /**
     * @return Definition[]
     */
    public function getCleanupDefinitions();

    /**
     * @param Definition $definition
     */
    public function addCleanupDefinition(Definition $definition);
}
