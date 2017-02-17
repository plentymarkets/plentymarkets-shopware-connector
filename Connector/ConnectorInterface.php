<?php

namespace PlentyConnector\Connector;

use PlentyConnector\Connector\Exception\MissingCommandException;
use PlentyConnector\Connector\Exception\MissingQueryException;
use PlentyConnector\Connector\ValueObject\Definition\Definition;

/**
 * Interface ConnectorInterface.
 */
interface ConnectorInterface
{
    /**
     * @param Definition $definition
     */
    public function addDefinition(Definition $definition);

    /**
     * @param int $queryType
     * @param null|string $objectType
     * @param null|string $identifier
     *
     * @throws MissingQueryException
     * @throws MissingCommandException
     */
    public function handle($queryType, $objectType = null, $identifier = null);
}
