<?php

namespace PlentyConnector\Connector\TransferObject\Product\Variation;

use DateTimeImmutable;
use PlentyConnector\Connector\TransferObject\Product\Barcode\Barcode;
use PlentyConnector\Connector\TransferObject\Product\Image\Image;
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
     * @var float
     */
    private $stock = 0;

    /**
     * @var string
     */
    private $number = '';

    /**
     * @var int
     */
    private $position = 0;

    /**
     * @var Barcode[]
     */
    private $barcodes = [];

    /**
     * @var string
     */
    private $model = '';

    /**
     * @var Image[]
     */
    private $images = [];

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
     * @var float
     */
    private $maximumOrderQuantity;

    /**
     * @var float
     */
    private $minimumOrderQuantity = 1.0;

    /**
     * @var float
     */
    private $intervalOrderQuantity = 1.0;

    /**
     * @var int
     */
    private $shippingTime = 0;

    /**
     * @var null|DateTimeImmutable
     */
    private $releaseDate;

    /**
     * @var int
     */
    private $width = 0;

    /**
     * @var int
     */
    private $height = 0;

    /**
     * @var int
     */
    private $length = 0;

    /**
     * @var int
     */
    private $weight = 0;

    /**
     * @var Property[]
     */
    private $properties = [];

    /**
     * @var Attribute[]
     */
    private $attributes = [];

    /**
     * @return bool
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * @param bool $active
     */
    public function setActive($active)
    {
        $this->active = $active;
    }

    /**
     * @return bool
     */
    public function isMain()
    {
        return $this->isMain;
    }

    /**
     * @param bool $isMain
     */
    public function setIsMain($isMain)
    {
        $this->isMain = $isMain;
    }

    /**
     * @return float
     */
    public function getStock()
    {
        return $this->stock;
    }

    /**
     * @param float $stock
     */
    public function setStock($stock)
    {
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
        $this->number = $number;
    }

    /**
     * @return int
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * @param int $position
     */
    public function setPosition($position)
    {
        $this->position = $position;
    }

    /**
     * @return Barcode[]
     */
    public function getBarcodes()
    {
        return $this->barcodes;
    }

    /**
     * @param Barcode[] $barcodes
     */
    public function setBarcodes(array $barcodes)
    {
        $this->barcodes = $barcodes;
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
        $this->model = $model;
    }

    /**
     * @return Image[]
     */
    public function getImages()
    {
        return $this->images;
    }

    /**
     * @param Image[] $images
     */
    public function setImages($images)
    {
        $this->images = $images;
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
        $this->content = $content;
    }

    /**
     * @return float
     */
    public function getMaximumOrderQuantity()
    {
        return $this->maximumOrderQuantity;
    }

    /**
     * @param float $maximumOrderQuantity
     */
    public function setMaximumOrderQuantity($maximumOrderQuantity)
    {
        $this->maximumOrderQuantity = $maximumOrderQuantity;
    }

    /**
     * @return float
     */
    public function getMinimumOrderQuantity()
    {
        return $this->minimumOrderQuantity;
    }

    /**
     * @param float $minimumOrderQuantity
     */
    public function setMinimumOrderQuantity($minimumOrderQuantity)
    {
        $this->minimumOrderQuantity = $minimumOrderQuantity;
    }

    /**
     * @return float
     */
    public function getIntervalOrderQuantity()
    {
        return $this->intervalOrderQuantity;
    }

    /**
     * @param float $intervalOrderQuantity
     */
    public function setIntervalOrderQuantity($intervalOrderQuantity)
    {
        $this->intervalOrderQuantity = $intervalOrderQuantity;
    }

    /**
     * @return int
     */
    public function getShippingTime()
    {
        return $this->shippingTime;
    }

    /**
     * @param int $shippingTime
     */
    public function setShippingTime($shippingTime)
    {
        $this->shippingTime = $shippingTime;
    }

    /**
     * @return null|DateTimeImmutable
     */
    public function getReleaseDate()
    {
        return $this->releaseDate;
    }

    /**
     * @param null|DateTimeImmutable $releaseDate
     */
    public function setReleaseDate(DateTimeImmutable $releaseDate = null)
    {
        $this->releaseDate = $releaseDate;
    }

    /**
     * @return int
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * @param int $width
     */
    public function setWidth($width)
    {
        $this->width = $width;
    }

    /**
     * @return int
     */
    public function getHeight()
    {
        return $this->height;
    }

    /**
     * @param int $height
     */
    public function setHeight($height)
    {
        $this->height = $height;
    }

    /**
     * @return int
     */
    public function getLength()
    {
        return $this->length;
    }

    /**
     * @param int $length
     */
    public function setLength($length)
    {
        $this->length = $length;
    }

    /**
     * @return int
     */
    public function getWeight()
    {
        return $this->weight;
    }

    /**
     * @param int $weight
     */
    public function setWeight($weight)
    {
        $this->weight = $weight;
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
    public function setProperties(array $properties)
    {
        $this->properties = $properties;
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
    public function setAttributes(array $attributes)
    {
        $this->attributes = $attributes;
    }
}
