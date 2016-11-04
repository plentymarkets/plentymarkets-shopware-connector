<?php

namespace PlentyConnector\Connector\Mapping;

use PlentyConnector\Connector\QueryBus\QueryFactoryInterface;
use PlentyConnector\Connector\ServiceBus\ServiceBusInterface;
use PlentyConnector\Connector\TransferObject\Definition\DefinitionInterface;
use PlentyConnector\Connector\TransferObject\MappedTransferObjectInterface;
use PlentyConnector\Connector\TransferObject\Mapping\Mapping;
use PlentyConnector\Connector\TransferObject\Mapping\MappingInterface;

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
     * @return MappingInterface[]
     */
    public function getMappingInformation()
    {
        $result = [];

        foreach ($this->definitions as $definition) {
            $originQuery = $this->queryFactory->create();
            $originTransferObjects = $this->queryBus->handle($originQuery);

            $destinationQuery = $this->queryFactory->create();
            $destinationTransferObjects = $this->queryBus->handle($destinationQuery);

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
     * @param DefinitionInterface[] $originTransferObjects
     * @param DefinitionInterface[] $destinationTransferObjects
     *
     * @return bool
     */
    private function isMappingComplete(array $originTransferObjects, array $destinationTransferObjects)
    {
        $diff = array_udiff($originTransferObjects, $destinationTransferObjects,
            function (
                MappedTransferObjectInterface $originTransferObjects,
                MappedTransferObjectInterface $destinationTransferObjects
            ) {
                if ($originTransferObjects->getIdentifier() === $destinationTransferObjects->getIdentifier()) {
                    return 0;
                }

                return 1;
            }
        );

        return (bool)$diff;
    }
}
