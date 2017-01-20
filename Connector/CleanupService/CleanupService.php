<?php

namespace PlentyConnector\Connector\CleanupService;

use Assert\Assertion;
use Exception;
use PlentyConnector\Connector\CommandBus\CommandFactory\CommandFactoryInterface;
use PlentyConnector\Connector\CommandBus\CommandFactory\Exception\MissingCommandException;
use PlentyConnector\Connector\CommandBus\CommandFactory\Exception\MissingCommandGeneratorException;
use PlentyConnector\Connector\CommandBus\CommandType;
use PlentyConnector\Connector\IdentityService\IdentityServiceInterface;
use PlentyConnector\Connector\QueryBus\QueryFactory\Exception\MissingQueryException;
use PlentyConnector\Connector\QueryBus\QueryFactory\Exception\MissingQueryGeneratorException;
use PlentyConnector\Connector\QueryBus\QueryFactory\QueryFactoryInterface;
use PlentyConnector\Connector\QueryBus\QueryType;
use PlentyConnector\Connector\ServiceBus\ServiceBusInterface;
use PlentyConnector\Connector\ValueObject\Definition\DefinitionInterface;
use PlentyConnector\Connector\ValueObject\Identity\IdentityInterface;
use PlentyConnector\Connector\TransferObject\TransferObjectInterface;
use Psr\Log\LoggerInterface;

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
    private $elements = [];

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * CleanupService constructor.
     *
     * @param ServiceBusInterface $queryBus
     * @param ServiceBusInterface $commandBus
     * @param QueryFactoryInterface $queryFactory
     * @param CommandFactoryInterface $commandFactory
     * @param IdentityServiceInterface $identityService
     * @param LoggerInterface $logger
     */
    public function __construct(
        ServiceBusInterface $queryBus,
        ServiceBusInterface $commandBus,
        QueryFactoryInterface $queryFactory,
        CommandFactoryInterface $commandFactory,
        IdentityServiceInterface $identityService,
        LoggerInterface $logger
    ) {
        $this->queryBus = $queryBus;
        $this->commandBus = $commandBus;
        $this->queryFactory = $queryFactory;
        $this->commandFactory = $commandFactory;
        $this->identityService = $identityService;
        $this->logger = $logger;
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
     */
    public function cleanup($objectType = null)
    {
        Assertion::nullOrString($objectType);

        $definitions = $this->getDefinitions($objectType);

        array_walk($definitions, function (DefinitionInterface $definition) {
            try {
                $foundElements = $this->collectObjectIdentifiers($definition);

                if (!$foundElements) {
                    $this->removeAllElements($definition);
                }
            } catch (Exception $exception) {
                $this->logger->emergency($exception->getMessage());
            }
        });

        try {
            $this->removeOrphanedElements();
        } catch (Exception $exception) {
            $this->logger->emergency($exception->getMessage());
        }
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
     * @throws MissingCommandException
     * @throws MissingCommandGeneratorException
     */
    private function removeAllElements(DefinitionInterface $definition)
    {
        $allIdentities = $this->identityService->findby([
            'adapterName' => $definition->getDestinationAdapterName(),
            'objectType' => $definition->getObjectType(),
        ]);

        array_walk($allIdentities, function (IdentityInterface $identity) use ($definition) {
            $this->commandBus->handle($this->commandFactory->create(
                $definition->getDestinationAdapterName(),
                $definition->getObjectType(),
                CommandType::REMOVE,
                $identity->getObjectIdentifier()
            ));
        });
    }

    /**
     * @throws MissingCommandException
     * @throws MissingCommandGeneratorException
     */
    private function removeOrphanedElements()
    {
        $groups = [];
        foreach ($this->elements as $element) {
            $groups[$element['adapterName'] . '_' . $element['type']][] = $element;
        }

        foreach ($groups as $group) {
            $adapterName = $group[0]['adapterName'];
            $objectType = $group[0]['type'];

            $identifiers = array_column($group, 'adapterIdentifier');

            $allIdentities = $this->identityService->findby([
                'adapterName' => $adapterName,
                'objectType' => $objectType,
            ]);

            $orphanedIdentities = array_filter($allIdentities, function(IdentityInterface $identity) use ($identifiers) {
                return !in_array($identity->getObjectIdentifier(), $identifiers, true);
            });

            array_walk($orphanedIdentities, function (IdentityInterface $identity) use ($adapterName, $objectType) {
                $this->commandBus->handle($this->commandFactory->create(
                    $adapterName,
                    $objectType,
                    CommandType::REMOVE,
                    $identity->getObjectIdentifier()
                ));
            });
        }
    }

    /**
     * @param DefinitionInterface $definition
     *
     * @throws MissingQueryException
     * @throws MissingQueryGeneratorException
     *
     * @return bool
     */
    private function collectObjectIdentifiers(DefinitionInterface $definition)
    {
        /**
         * @var TransferObjectInterface[] $objects
         */
        $objects = $this->queryBus->handle($this->queryFactory->create(
            $definition->getOriginAdapterName(),
            $definition->getObjectType(),
            QueryType::ALL
        ));

        if (empty($objects)) {
            return false;
        }

        array_walk($objects, function (TransferObjectInterface $transferObject) use ($definition) {
            $this->elements[] = [
                'adapterIdentifier' => $transferObject->getIdentifier(),
                'adapterName' => $definition->getDestinationAdapterName(),
                'type' => $transferObject->getType(),
            ];
        });

        return true;
    }
}
