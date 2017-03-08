<?php

namespace PlentyConnector\Connector\ValueObject\Mapping;

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
    private $originAdapterName = '';

    /**
     * origin transfer objects
     *
     * @var TransferObjectInterface[]
     */
    private $originTransferObjects = [];

    /**
     * destination adapter name.
     *
     * @var string
     */
    private $destinationAdapterName = '';

    /**
     * destination transfer objects
     *
     * @var TransferObjectInterface[]
     */
    private $destinationTransferObjects = [];

    /**
     * object type
     *
     * @var string
     */
    private $objectType = '';

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
        $this->objectType = $objectType;
    }
}
