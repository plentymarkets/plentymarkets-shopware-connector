<?php

namespace SystemConnector\IdentityService\Struct;

use SystemConnector\ValueObject\AbstractValueObject;

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

    public function getObjectIdentifier(): string
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

    public function getAdapterIdentifier(): string
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

    public function getAdapterName(): string
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

    /**
     * {@inheritdoc}
     */
    public function getClassProperties()
    {
        return [
            'objectIdentifier' => $this->getObjectIdentifier(),
            'objectType' => $this->getObjectType(),
            'adapterIdentifier' => $this->getAdapterIdentifier(),
            'adapterName' => $this->getAdapterName(),
        ];
    }
}
