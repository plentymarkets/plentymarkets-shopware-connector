<?php

namespace PlentyConnector\Connector\MappingService;

use Assert\Assertion;
use Doctrine\Common\Cache\Cache;
use PlentyConnector\Connector\ServiceBus\QueryFactory\Exception\MissingQueryException;
use PlentyConnector\Connector\ServiceBus\QueryFactory\Exception\MissingQueryGeneratorException;
use PlentyConnector\Connector\ServiceBus\QueryFactory\QueryFactoryInterface;
use PlentyConnector\Connector\ServiceBus\QueryType;
use PlentyConnector\Connector\ServiceBus\ServiceBusInterface;
use PlentyConnector\Connector\ValueObject\Definition\DefinitionInterface;
use PlentyConnector\Connector\TransferObject\TransferObjectInterface;
use PlentyConnector\Connector\ValueObject\Mapping\Mapping;

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
    private $serviceBus;

    /**
     * @var Cache
     */
    private $cache;

    /**
     * @var string
     */
    private $cacheKey = 'PlentyConnector_MappingInformations';

    /**
     * @var string
     */
    private $cacheLifetime = 86400;

    /**
     * MappingService constructor.
     *
     * @param QueryFactoryInterface $queryFactory
     * @param ServiceBusInterface $serviceBus
     * @param Cache $cache
     */
    public function __construct(
        QueryFactoryInterface $queryFactory,
        ServiceBusInterface $serviceBus,
        Cache $cache
    ) {
        $this->queryFactory = $queryFactory;
        $this->serviceBus = $serviceBus;
        $this->cache = $cache;
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
    public function getMappingInformation($objectType = null, $fresh = false)
    {
        Assertion::nullOrString($objectType);
        Assertion::boolean($fresh);

        if (!$fresh && $this->cache->contains($this->cacheKey)) {
            return $this->cache->fetch($this->cacheKey);
        }

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

        $this->cache->save($this->cacheKey, $result, $this->cacheLifetime);

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
     * @return TransferObjectInterface[]
     *
     * @throws MissingQueryGeneratorException
     * @throws MissingQueryException
     */
    private function query(DefinitionInterface $definition, $adapterName)
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

        usort($objects, function(TransferObjectInterface $a, TransferObjectInterface $b) {
            if (method_exists($a, 'getName') && method_exists($b, 'getName')) {
                $namea = $a->getName();
                $nameb = $b->getName();
            } else {
                $namea = $a->getIdentifier();
                $nameb = $b->getIdentifier();
            }

            $namea = trim($namea);
            $nameb = trim($nameb);

            return strnatcasecmp($namea, $nameb);
        });

        return $objects;
    }
}
