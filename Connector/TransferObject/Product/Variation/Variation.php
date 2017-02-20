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
     * @var string
     */
    private $model = '';

    /**
     * @var array
     */
    private $imageIdentifiers = [];

    /**
     * @var Price[]
     */
    private $prices = [];

    /**
     * @var float
     */
    private $purchasePrice = 0.0;

    /**
     * @var string
     */
    private $unitIdentifier = '';

    /**
     * @var float
     */
    private $content = 0.0;

    /**
     * @var null|int
     */
    private $maximumOrderQuantity;

    /**
     * @var int
     */
    private $minimumOrderQuantity = 1;

    /**
     * @var int
     */
    private $intervalOrderQuantity = 1;

    /**
     * @var null|int
     */
    private $width;

    /**
     * @var null|int
     */
    private $height;

    /**
     * @var null|int
     */
    private $length;

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
     * @return string
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * @param string $model
     */
    public function setModel($model)
    {
        Assertion::string($model);

        $this->model = $model;
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
     * @return float
     */
    public function getPurchasePrice()
    {
        return $this->purchasePrice;
    }

    /**
     * @param float $purchasePrice
     */
    public function setPurchasePrice($purchasePrice)
    {
        Assertion::float($purchasePrice);

        $this->purchasePrice = $purchasePrice;
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
     * @return null|int
     */
    public function getMaximumOrderQuantity()
    {
        return $this->maximumOrderQuantity;
    }

    /**
     * @param null|int $maximumOrderQuantity
     */
    public function setMaximumOrderQuantity($maximumOrderQuantity)
    {
        Assertion::nullOrInteger($maximumOrderQuantity);

        $this->maximumOrderQuantity = $maximumOrderQuantity;
    }

    /**
     * @return int
     */
    public function getMinimumOrderQuantity()
    {
        return $this->minimumOrderQuantity;
    }

    /**
     * @param int $minimumOrderQuantity
     */
    public function setMinimumOrderQuantity($minimumOrderQuantity)
    {
        Assertion::integer($minimumOrderQuantity);

        $this->minimumOrderQuantity = $minimumOrderQuantity;
    }

    /**
     * @return int
     */
    public function getIntervalOrderQuantity()
    {
        return $this->intervalOrderQuantity;
    }

    /**
     * @param int $intervalOrderQuantity
     */
    public function setIntervalOrderQuantity($intervalOrderQuantity)
    {
        Assertion::integer($intervalOrderQuantity);

        $this->intervalOrderQuantity = $intervalOrderQuantity;
    }

    /**
     * @return int|null
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * @param int|null $width
     */
    public function setWidth($width)
    {
        Assertion::nullOrInteger($width);

        $this->width = $width;
    }

    /**
     * @return int|null
     */
    public function getHeight()
    {
        return $this->height;
    }

    /**
     * @param int|null $height
     */
    public function setHeight($height)
    {
        Assertion::nullOrInteger($height);

        $this->height = $height;
    }

    /**
     * @return int|null
     */
    public function getLength()
    {
        return $this->length;
    }

    /**
     * @param int|null $length
     */
    public function setLength($length)
    {
        $this->length = $length;
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
