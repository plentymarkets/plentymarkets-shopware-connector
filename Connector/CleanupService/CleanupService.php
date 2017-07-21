<?php

namespace PlentyConnector\Connector\CleanupService;

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
     * @param ServiceBusInterface      $serviceBus
     * @param QueryFactoryInterface    $queryFactory
     * @param CommandFactoryInterface  $commandFactory
     * @param IdentityServiceInterface $identityService
     * @param LoggerInterface          $logger
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

        foreach ($definitions as $definition) {
            $foundElements = $this->collectObjectIdentifiers($definition);

            if (!$foundElements) {
                $this->removeAllElements($definition);
            }
        }

        $this->removeOrphanedElements();
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
            return strtolower($definition->getObjectType()) === strtolower($objectType) || null === $objectType;
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

        foreach ($objects as $transferObject) {
            $this->elements[] = [
                'adapterIdentifier' => $transferObject->getIdentifier(),
                'adapterName' => $definition->getDestinationAdapterName(),
                'type' => $transferObject->getType(),
            ];
        }

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
     * @return array
     */
    private function groupElementsByAdapterAndType()
    {
        $groups = [];

        foreach ($this->elements as $element) {
            $groups[$element['adapterName'] . '_' . $element['type']][] = $element;
        }

        return $groups;
    }

    /**
     * @param array $group
     *
     * @return Identity[]
     */
    private function findOrphanedIdentitiesByGroup(array $group)
    {
        $identifiers = array_column($group, 'adapterIdentifier');

        $allIdentities = $this->identityService->findBy([
            'adapterName' => $group[0]['adapterName'],
            'objectType' => $group[0]['type'],
        ]);

        return array_filter($allIdentities, function (Identity $identity) use ($identifiers) {
            return !in_array($identity->getObjectIdentifier(), $identifiers, true);
        });
    }

    private function removeOrphanedElements()
    {
        $groups = $this->groupElementsByAdapterAndType();

        foreach ($groups as $group) {
            $orphanedIdentities = $this->findOrphanedIdentitiesByGroup($group);

            foreach ($orphanedIdentities as $identity) {
                $this->serviceBus->handle($this->commandFactory->create(
                    $group[0]['adapterName'],
                    $group[0]['type'],
                    CommandType::REMOVE,
                    $identity->getObjectIdentifier()
                ));
            }
        }
    }
}
