<?php

namespace PlentyConnector\Connector;

use Assert\Assertion;
use PlentyConnector\Adapter\AdapterInterface;
use PlentyConnector\Connector\CommandBus\CommandFactory\CommandFactoryInterface;
use PlentyConnector\Connector\CommandBus\CommandFactory\Exception\MissingCommandException;
use PlentyConnector\Connector\CommandBus\CommandFactory\Exception\MissingCommandGeneratorException;
use PlentyConnector\Connector\CommandBus\CommandType;
use PlentyConnector\Connector\QueryBus\QueryFactory\Exception\MissingQueryException;
use PlentyConnector\Connector\QueryBus\QueryFactory\Exception\MissingQueryGeneratorException;
use PlentyConnector\Connector\QueryBus\QueryFactory\QueryFactoryInterface;
use PlentyConnector\Connector\QueryBus\QueryType;
use PlentyConnector\Connector\ServiceBus\ServiceBusInterface;
use PlentyConnector\Connector\ValueObject\Definition\DefinitionInterface;
use PlentyConnector\Connector\TransferObject\TransferObjectInterface;
use Psr\Log\LoggerInterface;

/**
 * TODO: error and exception handling
 * TODO: Refaktor
 *
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
     * @var QueryFactoryInterface
     */
    private $queryFactory;

    /**
     * @var CommandFactoryInterface
     */
    private $commandFactory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Connector constructor.
     *
     * @param ServiceBusInterface $queryBus
     * @param ServiceBusInterface $commandBus
     * @param ServiceBusInterface $eventBus
     * @param QueryFactoryInterface $queryFactory
     * @param CommandFactoryInterface $commandFactory
     * @param LoggerInterface $logger
     */
    public function __construct(
        ServiceBusInterface $queryBus,
        ServiceBusInterface $commandBus,
        ServiceBusInterface $eventBus,
        QueryFactoryInterface $queryFactory,
        CommandFactoryInterface $commandFactory,
        LoggerInterface $logger
    ) {
        $this->queryBus = $queryBus;
        $this->commandBus = $commandBus;
        $this->eventBus = $eventBus;
        $this->queryFactory = $queryFactory;
        $this->commandFactory = $commandFactory;
        $this->logger = $logger;
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

        $this->sortDefinitions();
    }

    /**
     *  sort definitions by priority. Highest priority needs to be on top of the array
     */
    private function sortDefinitions()
    {
        usort($this->definitions, function(DefinitionInterface $a, DefinitionInterface $b) {
            if ($a->getPriority() === $b->getPriority()) {
                return 0;
            }

            return ($a->getPriority() > $b->getPriority()) ? -1 : 1;
        });
    }

    /**
     * {@inheritdoc}
     */
    public function handle($queryType, $objectType = null, $identifier = null)
    {
        Assertion::InArray($queryType, QueryType::getAllTypes());
        Assertion::nullOrstring($objectType);

        if ($queryType === QueryType::ONE) {
            Assertion::notNull($identifier);
            Assertion::uuid($identifier);
        }

        $definitions = $this->getDefinitions($objectType);

        if (null === $definitions) {
            $definitions = [];
        }

        if (empty($definitions)) {
            $this->logger->warning('No definitions found');
        }

        array_walk($definitions, function (DefinitionInterface $definition) use ($queryType, $identifier) {
            $this->handleDefinition($definition, $queryType, $identifier);
        });
    }

    /**
     * {@inheritdoc}
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
     * @param integer $queryType
     * @param string|null $identifier
     *
     * @throws MissingQueryException
     * @throws MissingQueryGeneratorException
     * @throws MissingCommandException
     * @throws MissingCommandGeneratorException
     */
    private function handleDefinition(DefinitionInterface $definition, $queryType, $identifier = null)
    {
        /**
         * @var TransferObjectInterface[] $objects
         */
        $objects = $this->queryBus->handle($this->queryFactory->create(
            $definition->getOriginAdapterName(),
            $definition->getObjectType(),
            $queryType,
            $identifier
        ));

        if (null === $objects) {
            $objects = [];
        }

        array_walk($objects, function (TransferObjectInterface $object) use ($definition) {
            $this->commandBus->handle($this->commandFactory->create(
                $definition->getDestinationAdapterName(),
                $object->getType(),
                CommandType::HANDLE,
                $object
            ));
        });
    }
}
