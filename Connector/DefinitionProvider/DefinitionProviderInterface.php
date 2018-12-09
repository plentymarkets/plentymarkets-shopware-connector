<?php

namespace SystemConnector\DefinitionProvider;

use SystemConnector\DefinitionProvider\Struct\Definition;

interface DefinitionProviderInterface
{
    /**
     * @param null|string $objectType
     *
     * @return Definition[]
     */
    public function getConnectorDefinitions($objectType = null);

    /**
     * @param null $objectType
     *
     * @return Definition[]
     */
    public function getMappingDefinitions($objectType = null);

    /**
     * @return Definition[]
     */
    public function getCleanupDefinitions();
}
