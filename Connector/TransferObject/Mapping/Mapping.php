<?php

namespace PlentyConnector\Connector\TransferObject\Mapping;

use Assert\Assertion;
use PlentyConnector\Connector\TransferObject\MappedTransferObjectInterface;

/**
 * Class Mapping
 */
class Mapping implements MappingInterface
{
    const TYPE = 'Mapping';

    /**
     * origin adapter name.
     *
     * @var string
     */
    private $originAdapterName;

    /**
     * @var MappedTransferObjectInterface[]
     */
    private $originTransferObjects;

    /**
     * destination adapter name.
     *
     * @var string
     */
    private $destinationAdapterName;

    /**
     * @var MappedTransferObjectInterface[]
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
     * @param MappedTransferObjectInterface[] $originTransferObjects
     * @param string $destinationAdapterName
     * @param MappedTransferObjectInterface[] $destinationTransferObjects
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
        Assertion::allIsInstanceOf($originTransferObjects, MappedTransferObjectInterface::class);

        Assertion::string($destinationAdapterName);
        Assertion::allIsInstanceOf($destinationTransferObjects, MappedTransferObjectInterface::class);

        Assertion::string($objectType);

        $this->originAdapterName = $originAdapterName;
        $this->originTransferObjects = $originTransferObjects;
        $this->destinationAdapterName = $destinationAdapterName;
        $this->destinationTransferObjects = $destinationTransferObjects;
        $this->objectType = $objectType;
    }

    /**
     * {@inheritdoc}
     */
    public static function getType()
    {
        return self::TYPE;
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
