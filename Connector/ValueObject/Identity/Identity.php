<?php

namespace PlentyConnector\Connector\ValueObject\Identity;

use PlentyConnector\Connector\ValueObject\AbstractValueObject;

/**
 * Class Identity.
 */
class Identity extends AbstractValueObject
{
    /**
     * Identifier of the object.
     *
     * @var string
     */
    private $objectIdentifier = '';

    /**
     * TransferObject type.
     *
     * @var string
     */
    private $objectType = '';

    /**
     * Identifier inside the adapter domain.
     *
     * @var string
     */
    private $adapterIdentifier = '';

    /**
     * Adapter name.
     *
     * @var string
     */
    private $adapterName = '';

    /**
     * @return string
     */
    public function getObjectIdentifier()
    {
        return $this->objectIdentifier;
    }

    /**
     * @param string $objectIdentifier
     */
    public function setObjectIdentifier($objectIdentifier)
    {
        $this->objectIdentifier = $objectIdentifier;
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

    /**
     * @return string
     */
    public function getAdapterIdentifier()
    {
        return $this->adapterIdentifier;
    }

    /**
     * @param string $adapterIdentifier
     */
    public function setAdapterIdentifier($adapterIdentifier)
    {
        $this->adapterIdentifier = $adapterIdentifier;
    }

    /**
     * @return string
     */
    public function getAdapterName()
    {
        return $this->adapterName;
    }

    /**
     * @param string $adapterName
     */
    public function setAdapterName($adapterName)
    {
        $this->adapterName = $adapterName;
    }
}
