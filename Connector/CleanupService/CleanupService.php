<?php

namespace PlentyConnector\Connector\CleanupService;

use Exception;
use PlentyConnector\Connector\IdentityService\IdentityServiceInterface;
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
use PlentyConnector\Connector\ValueObject\Definition\Definition;
use PlentyConnector\Connector\ValueObject\Identity\Identity;
use Psr\Log\LoggerInterface;

/**
 * Class CleanupService.
 */
class CleanupService implements CleanupServiceInterface
{
    /**
     * @var Definition[]
     */
    private $definitions;

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
     * @param ServiceBusInterface $serviceBus
     * @param QueryFactoryInterface $queryFactory
     * @param CommandFactoryInterface $commandFactory
     * @param IdentityServiceInterface $identityService
     * @param LoggerInterface $logger
     */
    public function __construct(
        ServiceBusInterface $serviceBus,
        QueryFactoryInterface $queryFactory,
        CommandFactoryInterface $commandFactory,
        IdentityServiceInterface $identityService,
        LoggerInterface $logger
    ) {
        $this->serviceBus = $serviceBus;
        $this->queryFactory = $queryFactory;
        $this->commandFactory = $commandFactory;
        $this->identityService = $identityService;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function addDefinition(Definition $definition)
    {
        $this->definitions[] = $definition;
    }

    public function cleanup()
    {
        $definitions = $this->getDefinitions();

        array_walk($definitions, function (Definition $definition) {
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
     * @param null|string $objectType
     *
     * @return null|Definition[]
     */
    private function getDefinitions($objectType = null)
    {
        if (null === count($this->definitions)) {
            return [];
        }

        $definitions = array_filter($this->definitions, function (Definition $definition) use ($objectType) {
            return $definition->getObjectType() === $objectType || null === $objectType;
        });

        return $definitions;
    }

    /**
     * @param Definition $definition
     *
     * @throws MissingQueryException
     * @throws MissingQueryGeneratorException
     *
     * @return bool
     */
    private function collectObjectIdentifiers(Definition $definition)
    {
        /**
         * @var TransferObjectInterface[] $objects
         */
        $objects = $this->serviceBus->handle($this->queryFactory->create(
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

    /**
     * @param Definition $definition
     *
     * @throws MissingCommandException
     * @throws MissingCommandGeneratorException
     */
    private function removeAllElements(Definition $definition)
    {
        $allIdentities = $this->identityService->findBy([
            'adapterName' => $definition->getDestinationAdapterName(),
            'objectType' => $definition->getObjectType(),
        ]);

        array_walk($allIdentities, function (Identity $identity) use ($definition) {
            $this->serviceBus->handle($this->commandFactory->create(
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

            $allIdentities = $this->identityService->findBy([
                'adapterName' => $adapterName,
                'objectType' => $objectType,
            ]);

            $orphanedIdentities = array_filter($allIdentities, function (Identity $identity) use ($identifiers) {
                return !in_array($identity->getObjectIdentifier(), $identifiers, true);
            });

            array_walk($orphanedIdentities, function (Identity $identity) use ($adapterName, $objectType) {
                $this->serviceBus->handle($this->commandFactory->create(
                    $adapterName,
                    $objectType,
                    CommandType::REMOVE,
                    $identity->getObjectIdentifier()
                ));
            });
        }
    }
}
