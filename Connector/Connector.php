<?php

namespace PlentyConnector\Connector;

use PlentyConnector\Adapter\AdapterInterface;
use PlentyConnector\Connector\CommandBus\Command\CommandInterface;
use PlentyConnector\Connector\EventBus\Event\EventInterface;
use PlentyConnector\Connector\QueryBus\Query\QueryInterface;
use PlentyConnector\Connector\ServiceBus\ServiceBusInterface;
use PlentyConnector\Connector\TransferObject\TransferObjectInterface;
use PlentyConnector\Connector\Workflow\DefinitionInterface;

/**
 * Class Connector
 *
 * @package PlentyConnector\Connector
 */
class Connector implements ConnectorInterface
{
    /**
     * @var AdapterInterface[]
     */
    private $adapters;

    /**
     * @var DefinitionInterface[]
     */
    private $definitions;

    /**
     * @var ServiceBusInterface
     */
    private $queryBus;

    /**
     * @var ServiceBusInterface
     */
    private $commandBus;

    /**
     * @var ServiceBusInterface
     */
    private $eventBus;

    /**
     * Connector constructor.
     *
     * @param ServiceBusInterface $queryBus
     * @param ServiceBusInterface $commandBus
     * @param ServiceBusInterface $eventBus
     */
    public function __construct(
        ServiceBusInterface $queryBus,
        ServiceBusInterface $commandBus,
        ServiceBusInterface $eventBus
    )
    {
        $this->queryBus = $queryBus;
        $this->commandBus = $commandBus;
        $this->eventBus = $eventBus;
    }

    /**
     * @inheritdoc
     */
    public function addAdapter(AdapterInterface $adapters)
    {
        $this->adapters[] = $adapters;
    }

    /**
     * @inheritdoc
     */
    public function addDefinition(DefinitionInterface $definition)
    {
        $this->definitions[] = $definition;
    }

    /**
     * @inheritdoc
     */
    public function executeQuery(QueryInterface $query)
    {
        return $this->queryBus->handle($query);
    }

    /**
     * @inheritdoc
     */
    public function executeEvent(EventInterface $event)
    {
        $this->eventBus->handle($event);
    }

    /**
     * @param $objectType
     * @param $queryType
     */
    public function handle(ObjectTypeInterface $objectType, $queryType)
    {
        $definitions = $this->getDefinitions($objectType);

        array_map(function (DefinitionInterface $definition) {
            $this->handleDefinition($definition);
        }, $definitions);
    }

    /**
     * @inheritdoc
     */
    public function getDefinitions($type = null)
    {
        if (null === $this->definitions) {
            return [];
        }

        $definitions = array_filter($this->definitions, function (DefinitionInterface $definition) use ($type) {
            return ($definition->getObjectType() === $type || null === $type);
        });

        return $definitions;
    }

    /**
     * TODO: finalize
     *
     * @param DefinitionInterface $definition
     */
    private function handleDefinition(DefinitionInterface $definition)
    {
        $query = QueryFactory::create(
            $definition->getOriginAdapterName(),
            $definition->getObjectType(),
            $queryType
        );

        /**
         * @var TransferObjectInterface[] $objects
         */
        $objects = $this->queryBus->handle($query);

        if (null === $objects) {
            $objects = [];
        }

        foreach ($objects as $object) {
            $command = CommandFactory::create(
                $object,
                $definition->getDestinationAdapterName()
            );

            try {
                $this->commandBus->handle($command);
            } catch (ObjectDoesNotExistInAdapterDomainException $exception) {
                $this->handle(
                    $exception->getObjectType,
                    Connector::SINGLE_OBJECT
                );

                $this->executeCommand($command);
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function executeCommand(CommandInterface $command)
    {
        $this->commandBus->handle($command);
    }
}
