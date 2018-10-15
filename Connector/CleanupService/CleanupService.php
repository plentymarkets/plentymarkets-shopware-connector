<?php

namespace SystemConnector\CleanupService;

use Psr\Log\LoggerInterface;
use SystemConnector\CleanupService\CallbackLogHandler\CallbackLogHandler;
use SystemConnector\Console\OutputHandler\OutputHandlerInterface;
use SystemConnector\DefinitionProvider\DefinitionProviderInterface;
use SystemConnector\IdentityService\IdentityServiceInterface;
use SystemConnector\ServiceBus\CommandFactory\CommandFactoryInterface;
use SystemConnector\ServiceBus\CommandType;
use SystemConnector\ServiceBus\QueryFactory\QueryFactoryInterface;
use SystemConnector\ServiceBus\QueryType;
use SystemConnector\ServiceBus\ServiceBusInterface;
use SystemConnector\TransferObject\TransferObjectInterface;
use SystemConnector\ValueObject\Definition\Definition;
use SystemConnector\ValueObject\Identity\Identity;

class CleanupService implements CleanupServiceInterface
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
     * @var IdentityServiceInterface
     */
    private $identityService;

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

    /**
     * Array of all the found elements
     *
     * @var array
     */
    private $elements = [];

    /**
     * Will be set to true if the logger encounters an error, this will stop the cleanup process
     *
     * @var bool
     */
    private $error = false;

    public function __construct(
        ServiceBusInterface $serviceBus,
        QueryFactoryInterface $queryFactory,
        CommandFactoryInterface $commandFactory,
        IdentityServiceInterface $identityService,
        OutputHandlerInterface $outputHandler,
        DefinitionProviderInterface $definitionProvider,
        LoggerInterface $logger
    ) {
        $this->serviceBus = $serviceBus;
        $this->queryFactory = $queryFactory;
        $this->commandFactory = $commandFactory;
        $this->identityService = $identityService;
        $this->outputHandler = $outputHandler;
        $this->definitionProvider = $definitionProvider;
        $this->logger = $logger;
    }

    public function cleanup()
    {
        if (method_exists($this->logger, 'pushHandler')) {
            $this->logger->pushHandler(new CallbackLogHandler(function () {
                $this->error = true;
            }));
        }

        $definitions = $this->definitionProvider->getCleanupDefinitions();

        if (empty($definitions)) {
            $this->logger->notice('No cleanup definition found');
        }

        foreach ($definitions as $definition) {
            if ($this->hasErrors()) {
                continue;
            }

            $foundElements = $this->collectObjectIdentifiers($definition);

            if (!$foundElements) {
                $this->removeAllElements($definition);
            }
        }

        $this->removeOrphanedElements();
    }

    /**
     * @param Definition $definition
     *
     * @return bool
     */
    private function collectObjectIdentifiers(Definition $definition)
    {
        $this->outputHandler->writeLine(sprintf(
            'checking transfer objects for existence: Type: %s, %s -> %s',
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
            QueryType::ALL
        ));

        $found = false;

        if (empty($objects)) {
            return false;
        }

        foreach ($objects as $transferObject) {
            if (false === $found) {
                $found = true;
            }

            $this->elements[] = [
                'objectIdentifier' => $transferObject->getIdentifier(),
                'adapterName' => $definition->getDestinationAdapterName(),
                'type' => $transferObject->getType(),
            ];
        }

        return !(false === $found);
    }

    /**
     * @param Definition $definition
     */
    private function removeAllElements(Definition $definition)
    {
        if ($this->hasErrors()) {
            return;
        }

        $this->outputHandler->writeLine(sprintf(
            'remove all data for definition: Type: %s, %s -> %s',
            $definition->getObjectType(),
            $definition->getOriginAdapterName(),
            $definition->getDestinationAdapterName()
        ));

        $allIdentities = $this->identityService->findBy([
            'adapterName' => $definition->getDestinationAdapterName(),
            'objectType' => $definition->getObjectType(),
        ]);

        $this->outputHandler->startProgressBar(count($allIdentities));

        array_walk($allIdentities, function (Identity $identity) use ($definition) {
            $this->serviceBus->handle($this->commandFactory->create(
                $definition->getDestinationAdapterName(),
                $definition->getObjectType(),
                CommandType::REMOVE,
                -100,
                $identity->getObjectIdentifier()
            ));

            $this->outputHandler->advanceProgressBar();
        });

        $this->outputHandler->finishProgressBar();
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
        $identifiers = array_column($group, 'objectIdentifier');

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
        if ($this->hasErrors()) {
            return;
        }

        $groups = $this->groupElementsByAdapterAndType();

        if (empty($groups)) {
            return;
        }

        foreach ($groups as $group) {
            $orphanedIdentities = $this->findOrphanedIdentitiesByGroup($group);

            $this->outputHandler->writeLine(sprintf(
                'remove orphaned data for adapter: %s type: %s',
                $group[0]['adapterName'],
                $group[0]['type']
            ));

            $this->outputHandler->startProgressBar(count($orphanedIdentities));

            foreach ($orphanedIdentities as $identity) {
                $this->serviceBus->handle($this->commandFactory->create(
                    $group[0]['adapterName'],
                    $group[0]['type'],
                    CommandType::REMOVE,
                    -100,
                    $identity->getObjectIdentifier()
                ));

                $this->outputHandler->advanceProgressBar();
            }

            $this->outputHandler->finishProgressBar();
        }
    }

    private function hasErrors()
    {
        if (!$this->error) {
            return false;
        }

        $this->logger->error('cleanup process stopped due to an error while collecting data');

        return true;
    }
}
