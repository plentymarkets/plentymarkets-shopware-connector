<?php

namespace PlentyConnector\Connector;

use PlentyConnector\Connector\ValueObject\Definition\Definition;

interface ConnectorInterface
{
    /**
     * @param Definition $definition
     */
    public function addDefinition(Definition $definition);

    /**
     * @param int         $queryType
     * @param null|string $objectType
     * @param null|string $identifier
     */
    public function handle($queryType, $objectType = null, $identifier = null);
}
