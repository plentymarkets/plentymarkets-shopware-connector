<?php

namespace SystemConnector\MappingService\Struct;

use SystemConnector\TransferObject\TransferObjectInterface;

class Mapping
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

    public function getOriginAdapterName(): string
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
    public function getOriginTransferObjects(): array
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

    public function getDestinationAdapterName(): string
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
    public function getDestinationTransferObjects(): array
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

    public function getObjectType(): string
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
