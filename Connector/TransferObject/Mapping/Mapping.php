<?php

namespace PlentyConnector\Connector\TransferObject\Mapping;

use Assert\Assertion;
use PlentyConnector\Connector\TransferObject\MappedTransferObjectInterface;
use PlentyConnector\Connector\TransferObject\TransferObjectType;

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
     * Mapping constructor.
     *
     * @param string $originAdapterName
     * @param MappedTransferObjectInterface[] $originTransferObjects
     * @param string $destinationAdapterName
     * @param MappedTransferObjectInterface[] $destinationTransferObjects
     */
    public function __construct(
        $originAdapterName,
        array $originTransferObjects,
        $destinationAdapterName,
        array $destinationTransferObjects
    ) {
        Assertion::string($originAdapterName);
        Assertion::allIsInstanceOf($originTransferObjects, MappedTransferObjectInterface::class);

        Assertion::string($destinationAdapterName);
        Assertion::allIsInstanceOf($destinationTransferObjects, MappedTransferObjectInterface::class);

        $this->originAdapterName = $originAdapterName;
        $this->originTransferObjects = $originTransferObjects;
        $this->destinationAdapterName = $destinationAdapterName;
        $this->destinationTransferObjects = $destinationTransferObjects;
    }

    /**
     * @return string
     */
    public static function getType()
    {
        return TransferObjectType::MAPPING;
    }

    /**
     * {@inheritdoc}
     */
    public static function fromArray(array $params = [])
    {
        return new self(
            $params['originAdapterName'],
            $params['originTransferObjects'],
            $params['destinationAdapterName'],
            $params['destinationTransferObjects']
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
}
