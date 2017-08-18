<?php

namespace PlentyConnector\Connector;

use Assert\Assertion;
use PlentyConnector\Connector\ServiceBus\CommandFactory\CommandFactoryInterface;
use PlentyConnector\Connector\ServiceBus\CommandType;
use PlentyConnector\Connector\ServiceBus\QueryFactory\QueryFactoryInterface;
use PlentyConnector\Connector\ServiceBus\QueryType;
use PlentyConnector\Connector\ServiceBus\ServiceBusInterface;
use PlentyConnector\Connector\TransferObject\TransferObjectInterface;
use PlentyConnector\Connector\ValueObject\Definition\Definition;
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
     * @var null|Definition[]
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
    public function addDefinition(Definition $definition)
    {
        if (!$definition->isActive()) {
            return;
        }

        $this->definitions[] = $definition;

        $this->sortDefinitions();
    }

    /**
     * {@inheritdoc}
     */
    public function handle($queryType, $objectType = null, $identifier = null)
    {
        Assertion::inArray($queryType, QueryType::getAllTypes());
        Assertion::nullOrString($objectType);

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

        array_walk($definitions, function (Definition $definition) use ($queryType, $identifier) {
            $this->handleDefinition($definition, $queryType, $identifier);
        });
    }

    /**
     *  sort definitions by priority. Highest priority needs to be on top of the array
     */
    private function sortDefinitions()
    {
        usort($this->definitions, function (Definition $definitionLeft, Definition $definitionRight) {
            if ($definitionLeft->getPriority() === $definitionRight->getPriority()) {
                return 0;
            }

            return ($definitionLeft->getPriority() > $definitionRight->getPriority()) ? -1 : 1;
        });
    }

    /**
     * @param null $objectType
     *
     * @return Definition[]
     */
    private function getDefinitions($objectType = null)
    {
        if (null === count($this->definitions)) {
            return [];
        }

        $definitions = array_filter($this->definitions, function (Definition $definition) use ($objectType) {
            return strtolower($definition->getObjectType()) === strtolower($objectType) || null === $objectType;
        });

        return $definitions;
    }

    /**
     * @param Definition  $definition
     * @param int         $queryType
     * @param null|string $identifier
     */
    private function handleDefinition(Definition $definition, $queryType, $identifier = null)
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

        if (empty($objects)) {
            $objects = [];
        }

        foreach ($objects as $object) {
            $this->serviceBus->handle($this->commandFactory->create(
                $definition->getDestinationAdapterName(),
                $object->getType(),
                CommandType::HANDLE,
                $object
            ));
        }
    }
}
