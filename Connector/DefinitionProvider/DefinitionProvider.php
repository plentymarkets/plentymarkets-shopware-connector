<?php

namespace SystemConnector\DefinitionProvider;

use SystemConnector\ValueObject\Definition\Definition;

class DefinitionProvider implements DefinitionProviderInterface
{
    /**
     * @var Definition[]
     */
    private $connectorDefinitions = [];

    /**
     * @var Definition[]
     */
    private $mappingDefinitions = [];

    /**
     * @var Definition[]
     */
    private $cleanupDefinitions = [];

    /**
     * @param string| $objectType
     *
     * @return Definition[]
     */
    public function getConnectorDefinitions($objectType = null)
    {
        $definitions = array_filter($this->connectorDefinitions, function (Definition $definition) use ($objectType) {
            return strtolower($definition->getObjectType()) === strtolower($objectType) || null === $objectType;
        });

        return $definitions;
    }

    /**
     * @param Definition $definition
     */
    public function addConnectorDefinition(Definition $definition)
    {
        if (!$definition->isActive()) {
            return;
        }

        $this->connectorDefinitions[] = $definition;

        usort($this->connectorDefinitions, function (Definition $definitionLeft, Definition $definitionRight) {
            if ($definitionLeft->getPriority() === $definitionRight->getPriority()) {
                return 0;
            }

            return ($definitionLeft->getPriority() > $definitionRight->getPriority()) ? -1 : 1;
        });
    }

    /**
     * @param null $objectType
     *
     * @return Definition[]
     */
    public function getMappingDefinitions($objectType = null)
    {
        $definitions = array_filter($this->mappingDefinitions, function (Definition $definition) use ($objectType) {
            return strtolower($definition->getObjectType()) === strtolower($objectType) || null === $objectType;
        });

        return $definitions;
    }

    /**
     * @param Definition $definition
     */
    public function addMappingDefinition(Definition $definition)
    {
        $this->mappingDefinitions[] = $definition;
    }

    /**
     * @return Definition[]
     */
    public function getCleanupDefinitions()
    {
        return $this->cleanupDefinitions;
    }

    /**
     * @param Definition $definition
     */
    public function addCleanupDefinition(Definition $definition)
    {
        $this->cleanupDefinitions[] = $definition;
    }
}
