<?php

namespace PlentyConnector\Connector\Mapping;

use PlentyConnector\Connector\Exception\MissingQueryException;
use PlentyConnector\Connector\QueryBus\QueryFactory\QueryFactoryInterface;
use PlentyConnector\Connector\QueryBus\QueryType;
use PlentyConnector\Connector\ServiceBus\ServiceBusInterface;
use PlentyConnector\Connector\TransferObject\Definition\DefinitionInterface;
use PlentyConnector\Connector\TransferObject\MappedTransferObjectInterface;
use PlentyConnector\Connector\TransferObject\Mapping\Mapping;
use PlentyConnector\Connector\TransferObject\TransferObjectInterface;

/**
 * Class MappingService.
 */
class MappingService implements MappingServiceInterface
{
    /**
     * @var DefinitionInterface[]
     */
    private $definitions;

    /**
     * @var QueryFactoryInterface
     */
    private $queryFactory;

    /**
     * @var ServiceBusInterface
     */
    private $queryBus;

    /**
     * MappingService constructor.
     *
     * @param QueryFactoryInterface $queryFactory
     * @param ServiceBusInterface $queryBus
     */
    public function __construct(
        QueryFactoryInterface $queryFactory,
        ServiceBusInterface $queryBus
    ) {
        $this->queryFactory = $queryFactory;
        $this->queryBus = $queryBus;
    }

    /**
     * {@inheritdoc}
     */
    public function addDefinition(DefinitionInterface $definition)
    {
        $this->definitions[] = $definition;
    }

    /**
     * {@inheritdoc}
     */
    public function getMappingInformation($objectType = null)
    {
        $result = [];
        $definitions = $this->getDefinitions($objectType);

        array_walk($definitions, function (DefinitionInterface $definition) use (&$result) {
            $result[] = Mapping::fromArray([
                'originAdapterName' => $definition->getOriginAdapterName(),
                'originTransferObjects' => $this->query($definition, $definition->getOriginAdapterName()),
                'destinationAdapterName' => $definition->getDestinationAdapterName(),
                'destinationTransferObjects' => $this->query($definition, $definition->getDestinationAdapterName()),
                'objectType' => $definition->getObjectType()
            ]);
        });

        return $result;
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
     * @param string $adapterName
     *
     * @return TransferObjectInterface[]|null
     *
     * @throws \PlentyConnector\Connector\Exception\MissingQueryException
     */
    private function query(DefinitionInterface $definition, $adapterName)
    {
        $originQuery = $this->queryFactory->create(
            $adapterName,
            $definition->getObjectType(),
            QueryType::ALL
        );

        if (null === $originQuery) {
            throw MissingQueryException::fromDefinition($definition);
        }

        $objects = $this->queryBus->handle($originQuery);

        if (null === $objects) {
            $objects = [];
        }

        return array_filter($objects, function (TransferObjectInterface $object) use ($definition) {
            return $object::getType() === $definition->getObjectType()
                && is_subclass_of($object, MappedTransferObjectInterface::class);
        });
    }
}
