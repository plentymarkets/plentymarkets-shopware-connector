<?php

namespace PlentyConnector\Connector;

use Assert\Assertion;
use PlentyConnector\Connector\Console\OutputHandler\OutputHandlerInterface;
use PlentyConnector\Connector\DefinitionProvider\DefinitionProviderInterface;
use PlentyConnector\Connector\ServiceBus\CommandFactory\CommandFactoryInterface;
use PlentyConnector\Connector\ServiceBus\CommandType;
use PlentyConnector\Connector\ServiceBus\QueryFactory\QueryFactoryInterface;
use PlentyConnector\Connector\ServiceBus\QueryType;
use PlentyConnector\Connector\ServiceBus\ServiceBusInterface;
use PlentyConnector\Connector\TransferObject\TransferObjectInterface;
use PlentyConnector\Connector\ValueObject\Definition\Definition;
use Psr\Log\LoggerInterface;

class Connector implements ConnectorInterface
{
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
     * @var OutputHandlerInterface
     */
    private $outputHandler;

    /**
     * @var DefinitionProviderInterface
     */
    private $definitionProvider;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        ServiceBusInterface $serviceBus,
        QueryFactoryInterface $queryFactory,
        CommandFactoryInterface $commandFactory,
        OutputHandlerInterface $outputHandler,
        DefinitionProviderInterface $definitionProvider,
        LoggerInterface $logger
    ) {
        $this->serviceBus = $serviceBus;
        $this->queryFactory = $queryFactory;
        $this->commandFactory = $commandFactory;
        $this->outputHandler = $outputHandler;
        $this->definitionProvider = $definitionProvider;
        $this->logger = $logger;
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

        $definitions = $this->definitionProvider->getConnectorDefinitions($objectType);

        if (empty($definitions)) {
            $this->logger->notice('No connectordefinition found');
        }

        array_walk($definitions, function (Definition $definition) use ($queryType, $identifier) {
            $this->handleDefinition($definition, $queryType, $identifier);
        });
    }

    /**
     * @param Definition  $definition
     * @param int         $queryType
     * @param null|string $identifier
     */
    private function handleDefinition(Definition $definition, $queryType, $identifier = null)
    {
        $this->outputHandler->writeLine(sprintf(
            'handling definition: Type: %s, %s -> %s',
            $definition->getObjectType(),
            $definition->getOriginAdapterName(),
            $definition->getDestinationAdapterName()
        ));

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
                $definition->getPriority(),
                $object
            ));
        }
    }
}
