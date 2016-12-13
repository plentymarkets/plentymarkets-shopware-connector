<?php

namespace PlentyConnector\Connector;

use Assert\Assertion;
use PlentyConnector\Adapter\AdapterInterface;
use PlentyConnector\Connector\CommandBus\Command\CommandInterface;
use PlentyConnector\Connector\CommandBus\CommandFactory\CommandFactory;
use PlentyConnector\Connector\EventBus\Event\EventInterface;
use PlentyConnector\Connector\Exception\MissingQueryException;
use PlentyConnector\Connector\QueryBus\Query\QueryInterface;
use PlentyConnector\Connector\QueryBus\QueryFactory\QueryFactory;
use PlentyConnector\Connector\QueryBus\QueryType;
use PlentyConnector\Connector\ServiceBus\ServiceBusInterface;
use PlentyConnector\Connector\TransferObject\Definition\DefinitionInterface;
use PlentyConnector\Connector\TransferObject\TransferObjectInterface;
use PlentyConnector\Connector\TransferObject\TransferObjectType;

/**
 * Class Connector.
 */
class Connector implements ConnectorInterface
{
    /**
     * @var AdapterInterface[]|null
     */
    private $adapters = [];

    /**
     * @var DefinitionInterface[]|null
     */
    private $definitions = [];

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
     * @var QueryFactory
     */
    private $queryFactory;

    /**
     * @var CommandFactory
     */
    private $commandFactory;

    /**
     * Connector constructor.
     *
     * @param ServiceBusInterface $queryBus
     * @param ServiceBusInterface $commandBus
     * @param ServiceBusInterface $eventBus
     * @param QueryFactory $queryFactory
     * @param CommandFactory $commandFactory
     */
    public function __construct(
        ServiceBusInterface $queryBus,
        ServiceBusInterface $commandBus,
        ServiceBusInterface $eventBus,
        QueryFactory $queryFactory,
        CommandFactory $commandFactory
    ) {
        $this->queryBus = $queryBus;
        $this->commandBus = $commandBus;
        $this->eventBus = $eventBus;
        $this->queryFactory = $queryFactory;
        $this->commandFactory = $commandFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function addAdapter(AdapterInterface $adapters)
    {
        $this->adapters[] = $adapters;
    }

    /**
     * {@inheritdoc}
     */
    public function addDefinition(DefinitionInterface $definition)
    {
        $this->definitions[] = $definition;
    }

    /**
     * {@inheritdoc}
     */
    public function executeQuery(QueryInterface $query)
    {
        return $this->queryBus->handle($query);
    }

    /**
     * {@inheritdoc}
     */
    public function executeEvent(EventInterface $event)
    {
        $this->eventBus->handle($event);
    }

    /**
     * @param string $objectType
     * @param int $queryType
     * @param string|null $identifier
     *
     * @throws MissingQueryException
     */
    public function handle($objectType, $queryType, $identifier = null)
    {
        Assertion::inArray($objectType, TransferObjectType::getAllTypes());
        Assertion::inArray($queryType, QueryType::getAllTypes());

        if ($queryType === QueryType::ONE) {
            Assertion::notNull($identifier);
            Assertion::uuid($identifier);
        }

        $definitions = $this->getDefinitions($objectType);

        if (null === $definitions) {
            $definitions = [];
        }

        array_map(function (DefinitionInterface $definition) use ($queryType, $identifier) {
            $this->handleDefinition($definition, $queryType, $identifier);
        }, $definitions);
    }

    /**
     * @param string|null $type
     *
     * @return DefinitionInterface[]|null
     */
    private function getDefinitions($type = null)
    {
        if (null === count($this->definitions)) {
            return [];
        }

        $definitions = array_filter($this->definitions, function (DefinitionInterface $definition) use ($type) {
            return $definition->getObjectType() === $type || null === $type;
        });

        return $definitions;
    }

    /**
     * @param DefinitionInterface $definition
     * @param int $queryType
     * @param string|null $identifier
     *
     * @throws MissingQueryException
     */
    private function handleDefinition(DefinitionInterface $definition, $queryType, $identifier = null)
    {
        $query = $this->queryFactory->create(
            $definition->getOriginAdapterName(),
            $definition->getObjectType(),
            $queryType,
            $identifier
        );

        if (null === $query) {
            throw MissingQueryException::fromDefinition($definition);
        }

        /**
         * @var TransferObjectInterface[] $objects
         */
        $objects = $this->queryBus->handle($query);

        if (null === $objects) {
            $objects = [];
        }

        array_walk($objects, function (TransferObjectInterface $object) use ($definition) {
            $command = $this->commandFactory->create($object, $definition->getDestinationAdapterName());

            $this->handleCommand($command);
        });
    }

    /**
     * @param CommandInterface $command
     */
    private function handleCommand(CommandInterface $command)
    {
        try {
            $this->commandBus->handle($command);
        } catch (\Exception $exception) {
            // TODO: finalize
        }
    }

    /**
     * {@inheritdoc}
     */
    public function executeCommand(CommandInterface $command)
    {
        $this->commandBus->handle($command);
    }
}
