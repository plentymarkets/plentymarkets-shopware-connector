<?php

namespace PlentyConnector\Connector;

use Assert\Assertion;
use PlentyConnector\Connector\ServiceBus\CommandFactory\CommandFactoryInterface;
use PlentyConnector\Connector\ServiceBus\CommandFactory\Exception\MissingCommandException;
use PlentyConnector\Connector\ServiceBus\CommandFactory\Exception\MissingCommandGeneratorException;
use PlentyConnector\Connector\ServiceBus\CommandType;
use PlentyConnector\Connector\ServiceBus\QueryFactory\Exception\MissingQueryException;
use PlentyConnector\Connector\ServiceBus\QueryFactory\Exception\MissingQueryGeneratorException;
use PlentyConnector\Connector\ServiceBus\QueryFactory\QueryFactoryInterface;
use PlentyConnector\Connector\ServiceBus\QueryType;
use PlentyConnector\Connector\ServiceBus\ServiceBusInterface;
use PlentyConnector\Connector\TransferObject\TransferObjectInterface;
use PlentyConnector\Connector\ValueObject\Definition\DefinitionInterface;
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
     * @var DefinitionInterface[]|null
     */
    private $definitions = [];

    /**
     * @var ServiceBusInterface
     */
    private $serviceBus;

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
     * @param ServiceBusInterface     $serviceBus
     * @param QueryFactoryInterface   $queryFactory
     * @param CommandFactoryInterface $commandFactory
     * @param LoggerInterface         $logger
     */
    public function __construct(
        ServiceBusInterface $serviceBus,
        QueryFactoryInterface $queryFactory,
        CommandFactoryInterface $commandFactory,
        LoggerInterface $logger
    ) {
        $this->serviceBus = $serviceBus;
        $this->queryFactory = $queryFactory;
        $this->commandFactory = $commandFactory;
        $this->logger = $logger;
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
     *  sort definitions by priority. Highest priority needs to be on top of the array
     */
    private function sortDefinitions()
    {
        usort($this->definitions, function (DefinitionInterface $a, DefinitionInterface $b) {
            if ($a->getPriority() === $b->getPriority()) {
                return 0;
            }

            return ($a->getPriority() > $b->getPriority()) ? -1 : 1;
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
     * @param int                 $queryType
     * @param string|null         $identifier
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
        $objects = $this->serviceBus->handle($this->queryFactory->create(
            $definition->getOriginAdapterName(),
            $definition->getObjectType(),
            $queryType,
            $identifier
        ));

        if (null === $objects) {
            $objects = [];
        }

        array_walk($objects, function (TransferObjectInterface $object) use ($definition) {
            $this->serviceBus->handle($this->commandFactory->create(
                $definition->getDestinationAdapterName(),
                $object->getType(),
                CommandType::HANDLE,
                $object
            ));
        });
    }
}
