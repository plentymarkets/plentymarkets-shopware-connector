<?php

namespace PlentyConnector\Connector\CleanupService;

use PlentyConnector\Connector\ValueObject\Definition\DefinitionInterface;

/**
 * Interface CleanupServiceInterface.
 */
interface CleanupServiceInterface
{
    /**
     * @param DefinitionInterface $definition
     */
    public function addDefinition(DefinitionInterface $definition);

    /**
     * @param string|null $objectType
     */
    public function cleanup($objectType = null);
}
