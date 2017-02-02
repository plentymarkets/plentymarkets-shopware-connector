<?php

namespace PlentyConnector\Connector\ValueObject\Mapping;

use Assert\Assertion;
use PlentyConnector\Connector\TransferObject\TransferObjectInterface;

/**
 * Class Mapping
 */
class Mapping implements MappingInterface
{
    /**
     * origin adapter name.
     *
     * @var string
     */
    private $originAdapterName;

    /**
     * @var TransferObjectInterface[]
     */
    private $originTransferObjects;

    /**
     * destination adapter name.
     *
     * @var string
     */
    private $destinationAdapterName;

    /**
     * @var TransferObjectInterface[]
     */
    private $destinationTransferObjects;

    /**
     * @var string
     */
    private $objectType;

    /**
     * Mapping constructor.
     *
     * @param string $originAdapterName
     * @param TransferObjectInterface[] $originTransferObjects
     * @param string $destinationAdapterName
     * @param TransferObjectInterface[] $destinationTransferObjects
     * @param string $objectType
     */
    public function __construct(
        $originAdapterName,
        array $originTransferObjects,
        $destinationAdapterName,
        array $destinationTransferObjects,
        $objectType
    ) {
        Assertion::string($originAdapterName);
        Assertion::allIsInstanceOf($originTransferObjects, TransferObjectInterface::class);

        Assertion::string($destinationAdapterName);
        Assertion::allIsInstanceOf($destinationTransferObjects, TransferObjectInterface::class);

        Assertion::string($objectType);
        Assertion::notBlank($objectType);

        $this->originAdapterName = $originAdapterName;
        $this->originTransferObjects = $originTransferObjects;
        $this->destinationAdapterName = $destinationAdapterName;
        $this->destinationTransferObjects = $destinationTransferObjects;
        $this->objectType = $objectType;
    }

    /**
     * {@inheritdoc}
     */
    public static function fromArray(array $params = [])
    {
        Assertion::allInArray(array_keys($params), [
            'originAdapterName',
            'originTransferObjects',
            'destinationAdapterName',
            'destinationTransferObjects',
            'objectType',
        ]);

        return new self(
            $params['originAdapterName'],
            $params['originTransferObjects'],
            $params['destinationAdapterName'],
            $params['destinationTransferObjects'],
            $params['objectType']
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getOriginAdapterName()
    {
        return $this->originAdapterName;
    }

    /**
     * {@inheritdoc}
     */
    public function getOriginTransferObjects()
    {
        return $this->originTransferObjects;
    }

    /**
     * {@inheritdoc}
     */
    public function getDestinationAdapterName()
    {
        return $this->destinationAdapterName;
    }

    /**
     * {@inheritdoc}
     */
    public function getDestinationTransferObjects()
    {
        return $this->destinationTransferObjects;
    }

    /**
     * {@inheritdoc}
     */
    public function getObjectType()
    {
        return $this->objectType;
    }
}
