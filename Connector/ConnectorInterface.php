<?php

namespace PlentyConnector\Connector;

use PlentyConnector\Adapter\AdapterInterface;
use PlentyConnector\Connector\CommandBus\Command\CommandInterface;
use PlentyConnector\Connector\EventBus\Event\EventInterface;
use PlentyConnector\Connector\QueryBus\Query\QueryInterface;
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
     * @param $type
     *
     * @return DefinitionInterface[]
     */
    public function getDefinitions($type = null);

    /**
     * @param CommandInterface $command
     */
    public function executeCommand(CommandInterface $command);

    /**
     * @param QueryInterface $query
     *
     * @return mixed
     */
    public function executeQuery(QueryInterface $query);

    /**
     * @param EventInterface $event
     */
    public function executeEvent(EventInterface $event);
}
