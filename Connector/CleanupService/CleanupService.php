<?php

namespace PlentyConnector\Connector\CleanupService;

use Assert\Assertion;
use PlentyConnector\Connector\CommandBus\CommandFactory\CommandFactoryInterface;
use PlentyConnector\Connector\CommandBus\CommandType;
use PlentyConnector\Connector\Exception\MissingCommandException;
use PlentyConnector\Connector\Exception\MissingQueryException;
use PlentyConnector\Connector\IdentityService\IdentityServiceInterface;
use PlentyConnector\Connector\QueryBus\QueryFactory\QueryFactoryInterface;
use PlentyConnector\Connector\QueryBus\QueryType;
use PlentyConnector\Connector\ServiceBus\ServiceBusInterface;
use PlentyConnector\Connector\TransferObject\Definition\DefinitionInterface;
use PlentyConnector\Connector\TransferObject\Identity\IdentityInterface;
use PlentyConnector\Connector\TransferObject\SynchronizedTransferObjectInterface;

/**
 * Class CleanupService.
 */
class CleanupService implements CleanupServiceInterface
{
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
     * @var QueryFactoryInterface
     */
    private $queryFactory;

    /**
     * @var CommandFactoryInterface
     */
    private $commandFactory;

    /**
     * @var IdentityServiceInterface
     */
    private $identityService;

    /**
     * @var array
     */
    private $objectIdentifiers = [];

    /**
     * CleanupService constructor.
     *
     * @param ServiceBusInterface $queryBus
     * @param ServiceBusInterface $commandBus
     * @param CommandFactoryInterface $commandFactory
     * @param QueryFactoryInterface $queryFactory
     * @param IdentityServiceInterface $identityService
     */
    public function __construct(
        ServiceBusInterface $queryBus,
        ServiceBusInterface $commandBus,
        QueryFactoryInterface $queryFactory,
        CommandFactoryInterface $commandFactory,
        IdentityServiceInterface $identityService
    ) {
        $this->queryBus = $queryBus;
        $this->commandBus = $commandBus;
        $this->queryFactory = $queryFactory;
        $this->commandFactory = $commandFactory;
        $this->identityService = $identityService;
    }

    /**
     * {@inheritdoc}
     */
    public function addDefinition(DefinitionInterface $definition)
    {
        $this->definitions[] = $definition;
    }

    /**
     * @param string|null $objectType
     *
     * @throws MissingQueryException
     * @throws MissingCommandException
     */
    public function cleanup($objectType = null)
    {
        Assertion::nullOrString($objectType);

        $definitions = $this->getDefinitions($objectType);

        /*
        array_walk($definitions, function (DefinitionInterface $definition) {
            $this->collectObjectIdentifiers($definition);
        });

        foreach ($this->objectIdentifiers as $identifierArray) {

        }
        */
    }

    /**
     * @param string|null $objectType
     *
     * @return DefinitionInterface[]|null
     */
    private function getDefinitions($objectType = null)
    {
        if (null === count($this->definitions)) {
            return [];
        }

        $definitions = array_filter($this->definitions, function (DefinitionInterface $definition) use ($objectType) {
            return $definition->getObjectType() === $objectType || null === $objectType;
        });

        return $definitions;
    }

    /**
     * @param DefinitionInterface $definition
     *
     * @throws MissingQueryException
     */
    private function collectObjectIdentifiers(DefinitionInterface $definition)
    {
        /*
        $query = $this->queryFactory->create(
            $definition->getOriginAdapterName(),
            $definition->getObjectType(),
            QueryType::ALL
        );

        if (null === $query) {
            throw MissingQueryException::fromDefinition($definition);
        }
        */

        /**
         * @var SynchronizedTransferObjectInterface[] $objects
         */
        /*
        $objects = $this->queryBus->handle($query);

        if (null === $objects) {
            return;
        }

        array_walk($objects, function (SynchronizedTransferObjectInterface $transferObject) use ($definition) {
            $this->objectIdentifiers[$transferObject->getType()][] = Element::fromArray([
                'identifier' => $transferObject->getIdentifier(),
                'definition' => $definition
            ]);
        });
        */
    }

    /**
     * @param Elements[] $elements
     *
     * @throws MissingQueryException
     * @throws MissingCommandException
     */
    private function handleObjectCleanup(array $elements)
    {
        /*
        $identities = $this->identityService->findby([
            'adapterName' => $definition->getDestinationAdapterName(),
            'objectType' => $definition->getObjectType(),
        ]);

        array_filter($identities, function (IdentityInterface $identity) use ($objects) {
            return !array_key_exists($identity->getObjectIdentifier(), $objects);
        });

        array_walk($elements, function (Element $element) {

        });

        array_flip($objects);

        array_walk($identities, function (SynchronizedTransferObjectInterface $transferObject) use ($definition) {
            $command = $this->commandFactory->create(
                $transferObject,
                $definition->getDestinationAdapterName(),
                CommandType::REMOVE
            );

            if (null === $command) {
                throw MissingCommandException::fromDefinition($definition);
            }

            $this->commandBus->handle($command);
        });
        */
    }
}
