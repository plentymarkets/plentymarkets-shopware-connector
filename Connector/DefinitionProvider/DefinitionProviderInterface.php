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
    public function getConnectorDefinitions($objectType = null): array;

    /**
     * @param null $objectType
     *
     * @return Definition[]
     */
    public function getMappingDefinitions($objectType = null): array;

    /**
     * @return Definition[]
     */
    public function getCleanupDefinitions(): array;
}
