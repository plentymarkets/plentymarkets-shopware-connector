<?php

namespace PlentyConnector\Connector\ValueObject\Mapping;

use Assert\Assertion;
use PlentyConnector\Connector\TransferObject\TransferObjectInterface;
use PlentyConnector\Connector\ValueObject\AbstractValueObject;

/**
 * Class Mapping
 */
class Mapping extends AbstractValueObject
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
     * @return string
     */
    public function getOriginAdapterName()
    {
        return $this->originAdapterName;
    }

    /**
     * @param string $originAdapterName
     */
    public function setOriginAdapterName($originAdapterName)
    {
        Assertion::string($originAdapterName);

        $this->originAdapterName = $originAdapterName;
    }

    /**
     * @return TransferObjectInterface[]
     */
    public function getOriginTransferObjects()
    {
        return $this->originTransferObjects;
    }

    /**
     * @param TransferObjectInterface[] $originTransferObjects
     */
    public function setOriginTransferObjects($originTransferObjects)
    {
        Assertion::allIsInstanceOf($originTransferObjects, TransferObjectInterface::class);

        $this->originTransferObjects = $originTransferObjects;
    }

    /**
     * @return string
     */
    public function getDestinationAdapterName()
    {
        return $this->destinationAdapterName;
    }

    /**
     * @param string $destinationAdapterName
     */
    public function setDestinationAdapterName($destinationAdapterName)
    {
        Assertion::string($destinationAdapterName);

        $this->destinationAdapterName = $destinationAdapterName;
    }

    /**
     * @return TransferObjectInterface[]
     */
    public function getDestinationTransferObjects()
    {
        return $this->destinationTransferObjects;
    }

    /**
     * @param TransferObjectInterface[] $destinationTransferObjects
     */
    public function setDestinationTransferObjects($destinationTransferObjects)
    {
        Assertion::allIsInstanceOf($destinationTransferObjects, TransferObjectInterface::class);

        $this->destinationTransferObjects = $destinationTransferObjects;
    }

    /**
     * @return string
     */
    public function getObjectType()
    {
        return $this->objectType;
    }

    /**
     * @param string $objectType
     */
    public function setObjectType($objectType)
    {
        Assertion::string($objectType);
        Assertion::notBlank($objectType);

        $this->objectType = $objectType;
    }
}
