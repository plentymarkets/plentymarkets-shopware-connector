<?php

namespace PlentyConnector\Connector\MappingService;

use Assert\Assertion;
use PlentyConnector\Connector\DefinitionProvider\DefinitionProviderInterface;
use PlentyConnector\Connector\ServiceBus\QueryFactory\QueryFactoryInterface;
use PlentyConnector\Connector\ServiceBus\QueryType;
use PlentyConnector\Connector\ServiceBus\ServiceBusInterface;
use PlentyConnector\Connector\TransferObject\TransferObjectInterface;
use PlentyConnector\Connector\ValidatorService\ValidatorServiceInterface;
use PlentyConnector\Connector\ValueObject\Definition\Definition;
use PlentyConnector\Connector\ValueObject\Mapping\Mapping;
use Psr\Log\LoggerInterface;

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
            $mapping = Mapping::fromArray([
                'originAdapterName' => $definition->getOriginAdapterName(),
                'originTransferObjects' => $this->query($definition, $definition->getOriginAdapterName()),
                'destinationAdapterName' => $definition->getDestinationAdapterName(),
                'destinationTransferObjects' => $this->query($definition, $definition->getDestinationAdapterName()),
                'objectType' => $definition->getObjectType(),
            ]);

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
    private function query(Definition $definition, $adapterName)
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
