<?php

namespace PlentyConnector\Connector\Mapping;

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
    public function getMappingInformation()
    {
        $result = [];

        foreach ($this->definitions as $definition) {
            $originQuery = $this->queryFactory->create(
                $definition->getOriginAdapterName(),
                $definition->getObjectType(),
                QueryType::ALL
            );

            $originTransferObjects = $this->queryBus->handle($originQuery);

            if (null === $originTransferObjects) {
                // TODO handle failure
            }

            $originTransferObjects = array_filter($originTransferObjects, function(TransferObjectInterface $object) use ($definition) {
                return $object::getType() === $definition->getObjectType() && is_subclass_of($object, MappedTransferObjectInterface::class);
            });

            $destinationQuery =$this->queryFactory->create(
                $definition->getDestinationAdapterName(),
                $definition->getObjectType(),
                QueryType::ALL
            );

            $destinationTransferObjects = $this->queryBus->handle($destinationQuery);

            if (null === $destinationTransferObjects) {
                // TODO handle failure
            }

            $destinationTransferObjects = array_filter($destinationTransferObjects, function(TransferObjectInterface $object) use ($definition) {
                return $object::getType() === $definition->getObjectType() && is_subclass_of($object, MappedTransferObjectInterface::class);
            });

            $result[] = Mapping::fromArray([
                'originAdapterName' => $definition->getOriginAdapterName(),
                'originTransferObjects' => $originTransferObjects,
                'destinationAdapterName' => $definition->getDestinationAdapterName(),
                'destinationTransferObjects' => $destinationTransferObjects,
                'isComplete' => $this->isMappingComplete($originTransferObjects, $destinationTransferObjects),
            ]);
        }

        return $result;
    }

    /**
     * @param MappedTransferObjectInterface[] $originTransferObjects
     * @param MappedTransferObjectInterface[] $destinationTransferObjects
     *
     * @return bool
     */
    private function isMappingComplete(array $originTransferObjects, array $destinationTransferObjects)
    {
        $missingMapping = array_filter($destinationTransferObjects,
            function(MappedTransferObjectInterface $object) use ($originTransferObjects) {
                foreach ($originTransferObjects as $originTransferObject) {
                    if ($object->getIdentifier() === $originTransferObject->getIdentifier()) {
                        return true;
                    }
                }

                return false;
            }
        );

        return !$missingMapping;
    }
}
