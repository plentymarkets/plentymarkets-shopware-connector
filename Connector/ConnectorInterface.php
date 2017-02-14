<?php

namespace PlentyConnector\Connector;

use PlentyConnector\Connector\Exception\MissingCommandException;
use PlentyConnector\Connector\Exception\MissingQueryException;
use PlentyConnector\Connector\ValueObject\Definition\DefinitionInterface;

/**
 * Interface ConnectorInterface.
 */
interface ConnectorInterface
{
    /**
     * @param DefinitionInterface $definition
     */
    public function addDefinition(DefinitionInterface $definition);

    /**
     * @param int $queryType
     * @param string|null $objectType
     * @param string|null $identifier
     *
     * @throws MissingQueryException
     * @throws MissingCommandException
     */
    public function handle($queryType, $objectType = null, $identifier = null);
}
