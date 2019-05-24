<?php

namespace SystemConnector\MappingService;

use Assert\Assertion;
use Psr\Log\LoggerInterface;
use SystemConnector\DefinitionProvider\DefinitionProviderInterface;
use SystemConnector\DefinitionProvider\Struct\Definition;
use SystemConnector\MappingService\Struct\Mapping;
use SystemConnector\ServiceBus\QueryFactory\QueryFactoryInterface;
use SystemConnector\ServiceBus\QueryType;
use SystemConnector\ServiceBus\ServiceBusInterface;
use SystemConnector\TransferObject\TransferObjectInterface;
use SystemConnector\ValidatorService\ValidatorServiceInterface;

class MappingService implements MappingServiceInterface
{
    /**
     * @var QueryFactoryInterface
     */
    private $queryFactory;

    /**
     * @var ServiceBusInterface
     */
    private $serviceBus;

    /**
     * @var ValidatorServiceInterface
     */
    private $validator;

    /**
     * @var DefinitionProviderInterface
     */
    private $definitionProvider;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        QueryFactoryInterface $queryFactory,
        ServiceBusInterface $serviceBus,
        ValidatorServiceInterface $validator,
        DefinitionProviderInterface $definitionProvider,
        LoggerInterface $logger
    ) {
        $this->queryFactory = $queryFactory;
        $this->serviceBus = $serviceBus;
        $this->validator = $validator;
        $this->definitionProvider = $definitionProvider;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function getMappingInformation($objectType = null)
    {
        Assertion::nullOrString($objectType);

        $definitions = $this->definitionProvider->getMappingDefinitions($objectType);

        if (empty($definitions)) {
            $this->logger->notice('No mappingdefinition found');
        }

        $result = [];

        foreach ($definitions as $definition) {
            $mapping = new Mapping();
            $mapping->setOriginAdapterName($definition->getOriginAdapterName());
            $mapping->setOriginTransferObjects($this->query($definition, $definition->getOriginAdapterName()));
            $mapping->setDestinationAdapterName($definition->getDestinationAdapterName());
            $mapping->setDestinationTransferObjects($this->query($definition, $definition->getDestinationAdapterName()));
            $mapping->setObjectType($definition->getObjectType());

            $this->validator->validate($mapping);

            $result[] = $mapping;
        }

        return $result;
    }

    /**
     * @param Definition $definition
     * @param string     $adapterName
     *
     * @return TransferObjectInterface[]
     */
    private function query(Definition $definition, $adapterName): array
    {
        $originQuery = $this->queryFactory->create(
            $adapterName,
            $definition->getObjectType(),
            QueryType::ALL
        );

        $objects = $this->serviceBus->handle($originQuery);

        if (empty($objects)) {
            $objects = [];
        }

        $result = [];
        foreach ($objects as $object) {
            if ($object->getType() !== $definition->getObjectType()) {
                continue;
            }

            $result[] = $object;
        }

        return $result;
    }
}
