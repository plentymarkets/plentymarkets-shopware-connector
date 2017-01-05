<?php

namespace PlentyConnector\Connector;

use PlentyConnector\Adapter\AdapterInterface;
use PlentyConnector\Connector\Exception\MissingCommandException;
use PlentyConnector\Connector\Exception\MissingQueryException;
use PlentyConnector\Connector\TransferObject\Definition\DefinitionInterface;

/**
 * Interface ConnectorInterface.
 */
interface ConnectorInterface
{
    /**
     * @param AdapterInterface $adapters
     */
    public function addAdapter(AdapterInterface $adapters);

    /**
     * @param DefinitionInterface $definition
     */
    public function addDefinition(DefinitionInterface $definition);

    /**
     * @param integer $queryType
     * @param string|null $objectType
     * @param string|null $identifier
     *
     * @throws MissingQueryException
     * @throws MissingCommandException
     */
    public function handle($queryType, $objectType = null, $identifier = null);
}
