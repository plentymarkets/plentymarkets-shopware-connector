<?php

namespace SystemConnector\TransferObject\Manufacturer;

use SystemConnector\TransferObject\AbstractTransferObject;
use SystemConnector\TransferObject\AttributableInterface;
use SystemConnector\ValueObject\Attribute\Attribute;

class Manufacturer extends AbstractTransferObject implements AttributableInterface
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
    public function getType(): string
    {
        return self::TYPE;
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    /**
     * {@inheritdoc}
     */
    public function setIdentifier($identifier)
    {
        $this->identifier = $identifier;
    }

    public function getName(): string
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
     * @return null|string
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
     * @return Attribute[]
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    public function setAttributes(array $attributes)
    {
        $this->attributes = $attributes;
    }

    /**
     * {@inheritdoc}
     */
    public function getClassProperties()
    {
        return [
            'identifier' => $this->getIdentifier(),
            'name' => $this->getName(),
            'logoIdentifier' => $this->getLogoIdentifier(),
            'link' => $this->getLink(),
            'attributes' => $this->getAttributes(),
        ];
    }
}
