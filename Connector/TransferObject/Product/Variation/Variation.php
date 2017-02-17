<?php

namespace PlentyConnector\Connector\TransferObject\Product\Variation;

use Assert\Assertion;
use PlentyConnector\Connector\TransferObject\Product\Price\Price;
use PlentyConnector\Connector\TransferObject\Product\Property\Property;
use PlentyConnector\Connector\ValueObject\AbstractValueObject;
use PlentyConnector\Connector\ValueObject\Attribute\Attribute;

/**
 * Class Variation.
 */
class Variation extends AbstractValueObject
{
    /**
     * @var bool
     */
    private $active = false;

    /**
     * @var bool
     */
    private $isMain = false;

    /**
     * @var int
     */
    private $stock = 0;

    /**
     * @var string
     */
    private $number = '';

    /**
     * @var array
     */
    private $imageIdentifiers = [];

    /**
     * @var Price[]
     */
    private $prices = [];

    /**
     * @var string
     */
    private $unitIdentifier;

    /**
     * @var float
     */
    private $content = 0.0;

    /**
     * @var string
     */
    private $packagingUnit = '';

    /**
     * @var Attribute[]
     */
    private $attributes = [];

    /**
     * @var Property[]
     */
    private $properties = [];

    /**
     * @return bool
     */
    public function isActive()
    {
        return $this->active;
    }

    /**
     * @param bool $active
     */
    public function setActive($active)
    {
        Assertion::boolean($active);

        $this->active = $active;
    }

    /**
     * @return bool
     */
    public function isIsMain()
    {
        return $this->isMain;
    }

    /**
     * @param bool $isMain
     */
    public function setIsMain($isMain)
    {
        Assertion::boolean($isMain);

        $this->isMain = $isMain;
    }

    /**
     * @return int
     */
    public function getStock()
    {
        return $this->stock;
    }

    /**
     * @param int $stock
     */
    public function setStock($stock)
    {
        Assertion::integer($stock);

        $this->stock = $stock;
    }

    /**
     * @return string
     */
    public function getNumber()
    {
        return $this->number;
    }

    /**
     * @param string $number
     */
    public function setNumber($number)
    {
        Assertion::string($number);

        $this->number = $number;
    }

    /**
     * @return array
     */
    public function getImageIdentifiers()
    {
        return $this->imageIdentifiers;
    }

    /**
     * @param array $imageIdentifiers
     */
    public function setImageIdentifiers($imageIdentifiers)
    {
        Assertion::allUuid($imageIdentifiers);

        $this->imageIdentifiers = $imageIdentifiers;
    }

    /**
     * @return Price[]
     */
    public function getPrices()
    {
        return $this->prices;
    }

    /**
     * @param Price[] $prices
     */
    public function setPrices($prices)
    {
        Assertion::allIsInstanceOf($prices, Price::class);

        $this->prices = $prices;
    }

    /**
     * @return string
     */
    public function getUnitIdentifier()
    {
        return $this->unitIdentifier;
    }

    /**
     * @param string $unitIdentifier
     */
    public function setUnitIdentifier($unitIdentifier)
    {
        Assertion::uuid($unitIdentifier);

        $this->unitIdentifier = $unitIdentifier;
    }

    /**
     * @return float
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @param float $content
     */
    public function setContent($content)
    {
        Assertion::float($content);

        $this->content = $content;
    }

    /**
     * @return string
     */
    public function getPackagingUnit()
    {
        return $this->packagingUnit;
    }

    /**
     * @param string $packagingUnit
     */
    public function setPackagingUnit($packagingUnit)
    {
        Assertion::string($packagingUnit);

        $this->packagingUnit = $packagingUnit;
    }

    /**
     * @return Attribute[]
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * @param Attribute[] $attributes
     */
    public function setAttributes($attributes)
    {
        Assertion::allIsInstanceOf($attributes, Attribute::class);

        $this->attributes = $attributes;
    }

    /**
     * @return Property[]
     */
    public function getProperties()
    {
        return $this->properties;
    }

    /**
     * @param Property[] $properties
     */
    public function setProperties($properties)
    {
        Assertion::allIsInstanceOf($properties, Property::class);

        $this->properties = $properties;
    }
}
