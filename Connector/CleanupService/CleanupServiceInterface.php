<?php

namespace PlentyConnector\Connector\CleanupService;

use PlentyConnector\Connector\ValueObject\Definition\Definition;

interface CleanupServiceInterface
{
    /**
     * @param Definition $definition
     */
    public function addDefinition(Definition $definition);

    public function cleanup();
}
