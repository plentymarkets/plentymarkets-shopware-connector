<?php

namespace SystemConnector\TransferObject\Product\Variation;

use DateTimeImmutable;
use SystemConnector\TransferObject\AbstractTransferObject;
use SystemConnector\TransferObject\AttributableInterface;
use SystemConnector\TransferObject\Product\Barcode\Barcode;
use SystemConnector\TransferObject\Product\Image\Image;
use SystemConnector\TransferObject\Product\Price\Price;
use SystemConnector\TransferObject\Product\Property\Property;
use SystemConnector\ValueObject\Attribute\Attribute;

class Variation extends AbstractTransferObject implements AttributableInterface
{
    const TYPE = 'Variation';

    /**
     * Identifier of the object.
     *
     * @var string
     */
    private $identifier = '';

    /**
     * @var string
     */
    private $productIdentifier = '';

    /**
     * @var bool
     */
    private $active = false;

    /**
     * @var bool
     */
    private $isMain = false;

    /**
     * @var string
     */
    private $number = '';

    /**
     * @var string
     */
    private $name = '';

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
    private $referenceAmount = 0.0;

    /**
     * @var bool
     */
    private $stockLimitation = false;

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
     * @var float
     */
    private $weight = 0.0;

    /**
     * @var Property[]
     */
    private $properties = [];

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
     * @return string
     */
    public function getIdentifier()
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

    /**
     * @return string
     */
    public function getProductIdentifier(): string
    {
        return $this->productIdentifier;
    }

    /**
     * @param string $productIdentifier
     */
    public function setProductIdentifier($productIdentifier)
    {
        $this->productIdentifier = $productIdentifier;
    }

    /**
     * @return bool
     */
    public function getActive(): bool
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
    public function isMain(): bool
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
     * @return string
     */
    public function getNumber(): string
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
     * @return string
     */
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
     * @return int
     */
    public function getPosition(): int
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
    public function getBarcodes(): array
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
    public function getModel(): string
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
    public function getImages(): array
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
    public function getPrices(): array
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
    public function getPurchasePrice(): float
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
    public function getUnitIdentifier(): string
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
    public function getContent(): float
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
    public function getReferenceAmount(): float
    {
        return $this->referenceAmount;
    }

    /**
     * @param float $referenceAmount
     */
    public function setReferenceAmount($referenceAmount)
    {
        $this->referenceAmount = $referenceAmount;
    }

    /**
     * @return bool
     */
    public function hasStockLimitation(): bool
    {
        return $this->stockLimitation;
    }

    /**
     * @param bool $stockLimitation
     */
    public function setStockLimitation($stockLimitation)
    {
        $this->stockLimitation = $stockLimitation;
    }

    /**
     * @return float
     */
    public function getMaximumOrderQuantity(): float
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
    public function getMinimumOrderQuantity(): float
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
    public function getIntervalOrderQuantity(): float
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
    public function getShippingTime(): int
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
    public function getWidth(): int
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
    public function getHeight(): int
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
    public function getLength(): int
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
     * @return float
     */
    public function getWeight(): float
    {
        return $this->weight;
    }

    /**
     * @param float $weight
     */
    public function setWeight($weight)
    {
        $this->weight = $weight;
    }

    /**
     * @return Property[]
     */
    public function getProperties(): array
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

    /**
     * {@inheritdoc}
     */
    public function getClassProperties()
    {
        return [
            'identifier' => $this->getIdentifier(),
            'productIdentifier' => $this->getProductIdentifier(),
            'active' => $this->getActive(),
            'main' => $this->isMain(),
            'number' => $this->getNumber(),
            'name' => $this->getName(),
            'position' => $this->getPosition(),
            'barcodes' => $this->getBarcodes(),
            'model' => $this->getModel(),
            'images' => $this->getImages(),
            'prices' => $this->getPrices(),
            'purchasePrice' => $this->getPurchasePrice(),
            'unitIdentifier' => $this->getUnitIdentifier(),
            'content' => $this->getContent(),
            'referenceAmount' => $this->getReferenceAmount(),
            'stockLimitation' => $this->hasStockLimitation(),
            'maximumOrderQuantity' => $this->getMaximumOrderQuantity(),
            'minimumOrderQuantity' => $this->getMinimumOrderQuantity(),
            'intervalOrderQuantity' => $this->getIntervalOrderQuantity(),
            'shippingTime' => $this->getShippingTime(),
            'releaseDate' => $this->getReleaseDate(),
            'width' => $this->getWidth(),
            'height' => $this->getHeight(),
            'length' => $this->getLength(),
            'weight' => $this->getWeight(),
            'properties' => $this->getProperties(),
            'attributes' => $this->getAttributes(),
        ];
    }
}
