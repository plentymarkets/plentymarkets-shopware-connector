<?php

namespace PlentyConnector\Connector\MappingService;

use Assert\Assertion;
use PlentyConnector\Connector\ServiceBus\QueryFactory\Exception\MissingQueryException;
use PlentyConnector\Connector\ServiceBus\QueryFactory\Exception\MissingQueryGeneratorException;
use PlentyConnector\Connector\ServiceBus\QueryFactory\QueryFactoryInterface;
use PlentyConnector\Connector\ServiceBus\QueryType;
use PlentyConnector\Connector\ServiceBus\ServiceBusInterface;
use PlentyConnector\Connector\TransferObject\TransferObjectInterface;
use PlentyConnector\Connector\ValidatorService\ValidatorServiceInterface;
use PlentyConnector\Connector\ValueObject\Definition\Definition;
use PlentyConnector\Connector\ValueObject\Mapping\Mapping;

/**
 * Class MappingService.
 */
class MappingService implements MappingServiceInterface
{
    /**
     * @var Definition[]
     */
    private $definitions;

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
     * MappingService constructor.
     *
     * @param QueryFactoryInterface     $queryFactory
     * @param ServiceBusInterface       $serviceBus
     * @param ValidatorServiceInterface $validator
     */
    public function __construct(
        QueryFactoryInterface $queryFactory,
        ServiceBusInterface $serviceBus,
        ValidatorServiceInterface $validator
    ) {
        $this->queryFactory = $queryFactory;
        $this->serviceBus = $serviceBus;
        $this->validator = $validator;
    }

    /**
     * {@inheritdoc}
     */
    public function addDefinition(Definition $definition)
    {
        $this->definitions[] = $definition;
    }

    /**
     * {@inheritdoc}
     */
    public function getMappingInformation($objectType = null)
    {
        Assertion::nullOrString($objectType);

        $result = [];
        $definitions = $this->getDefinitions($objectType);

        array_walk($definitions, function (Definition $definition) use (&$result) {
            $mapping = Mapping::fromArray([
                'originAdapterName' => $definition->getOriginAdapterName(),
                'originTransferObjects' => $this->query($definition, $definition->getOriginAdapterName()),
                'destinationAdapterName' => $definition->getDestinationAdapterName(),
                'destinationTransferObjects' => $this->query($definition, $definition->getDestinationAdapterName()),
                'objectType' => $definition->getObjectType(),
            ]);

            $this->validator->validate($mapping);

            $result[] = $mapping;
        });

        return $result;
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
     * @param string     $adapterName
     *
     * @throws MissingQueryGeneratorException
     * @throws MissingQueryException
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

        if (null === $objects) {
            $objects = [];
        }

        $objects = array_filter($objects, function (TransferObjectInterface $object) use ($definition) {
            return $object->getType() === $definition->getObjectType();
        });

        return $objects;
    }
}
