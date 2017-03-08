<?php

namespace PlentyConnector\Connector\TransferObject\Manufacturer;

use PlentyConnector\Connector\TransferObject\AbstractTransferObject;
use PlentyConnector\Connector\ValueObject\Attribute\Attribute;

/**
 * Class TransferObjects.
 */
class Manufacturer extends AbstractTransferObject
{
    const TYPE = 'Manufacturer';

    /**
     * @var string
     */
    private $identifier = '';

    /**
     * @var string
     */
    private $name = '';

    /**
     * @var null|string
     */
    private $logoIdentifier;

    /**
     * @var null|string
     */
    private $link;

    /**
     * @var Attribute[]
     */
    private $attributes = [];

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return self::TYPE;
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * @param string $identifier
     */
    public function setIdentifier($identifier)
    {
        $this->identifier = $identifier;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getLogoIdentifier()
    {
        return $this->logoIdentifier;
    }

    /**
     * @param null|string $logoIdentifier
     */
    public function setLogoIdentifier($logoIdentifier = null)
    {
        $this->logoIdentifier = $logoIdentifier;
    }

    /**
     * @return string
     */
    public function getLink()
    {
        return $this->link;
    }

    /**
     * @param null|string $link
     */
    public function setLink($link = null)
    {
        $this->link = $link;
    }

    /**
     * @return mixed
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * @param mixed $attributes
     */
    public function setAttributes(array $attributes)
    {
        $this->attributes = $attributes;
    }
}
