<?php

namespace PlentyConnector\Connector\CleanupService;

use PlentyConnector\Connector\ValueObject\Definition\Definition;

/**
 * Interface CleanupServiceInterface.
 */
interface CleanupServiceInterface
{
    /**
     * @param Definition $definition
     */
    public function addDefinition(Definition $definition);

    /**
     * @param null|string $objectType
     */
    public function cleanup($objectType = null);
}
