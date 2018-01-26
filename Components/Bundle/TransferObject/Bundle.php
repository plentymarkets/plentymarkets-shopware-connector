<?php

namespace PlentyConnector\Components\Bundle\TransferObject;

use DateTimeImmutable;
use PlentyConnector\Components\Bundle\TransferObject\BundleProduct\BundleProduct;
use PlentyConnector\Connector\TransferObject\AbstractTransferObject;
use PlentyConnector\Connector\TransferObject\Product\Price\Price;
use PlentyConnector\Connector\ValueObject\Attribute\Attribute;
use PlentyConnector\Connector\ValueObject\Translation\Translation;

/**
 * Class Bundle
 */
class Bundle extends AbstractTransferObject
{
    const TYPE = 'Bundle';

    /**
     * @var string
     */
    private $identifier = '';

    /**
     * @var bool
     */
    private $active = false;

    /**
     * @var string
     */
    private $productIdentifier = '';

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $number;

    /**
     * @var int
     */
    private $position = 0;

    /**
     * @var float
     */
    private $stock = 0;

    /**
     * @var bool
     */
    private $stockLimitation = false;

    /**
     * @var Price[]
     */
    private $prices = [];

    /**
     * @var string
     */
    private $vatRateIdentifier = '';

    /**
     * @var null|DateTimeImmutable
     */
    private $availableFrom;

    /**
     * @var null|DateTimeImmutable
     */
    private $availableTo;

    /**
     * @var BundleProduct[]
     */
    private $bundleProducts = [];

    /**
     * @var Attribute[]
     */
    private $attributes = [];

    /**
     * @var Translation[]
     */
    private $translations = [];

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
     * @param string $identifier
     */
    public function setIdentifier($identifier)
    {
        $this->identifier = $identifier;
    }

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
        $this->active = $active;
    }

    /**
     * @return string
     */
    public function getProductIdentifier()
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
     * @return bool
     */
    public function hasStockLimitation()
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
     * @return string
     */
    public function getVatRateIdentifier()
    {
        return $this->vatRateIdentifier;
    }

    /**
     * @param string $vatRateIdentifier
     */
    public function setVatRateIdentifier($vatRateIdentifier)
    {
        $this->vatRateIdentifier = $vatRateIdentifier;
    }

    /**
     * @return null|DateTimeImmutable
     */
    public function getAvailableFrom()
    {
        return $this->availableFrom;
    }

    /**
     * @param null|DateTimeImmutable $availableFrom
     */
    public function setAvailableFrom(DateTimeImmutable $availableFrom = null)
    {
        $this->availableFrom = $availableFrom;
    }

    /**
     * @return null|DateTimeImmutable
     */
    public function getAvailableTo()
    {
        return $this->availableTo;
    }

    /**
     * @param null|DateTimeImmutable $availableTo
     */
    public function setAvailableTo(DateTimeImmutable $availableTo = null)
    {
        $this->availableTo = $availableTo;
    }

    /**
     * @return BundleProduct[]
     */
    public function getBundleProducts()
    {
        return $this->bundleProducts;
    }

    /**
     * @param BundleProduct[] $bundleProducts
     */
    public function setBundleProducts($bundleProducts)
    {
        $this->bundleProducts = $bundleProducts;
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
        $this->attributes = $attributes;
    }

    /**
     * @return Translation[]
     */
    public function getTranslations()
    {
        return $this->translations;
    }

    /**
     * @param Translation[] $translations
     */
    public function setTranslations(array $translations)
    {
        $this->translations = $translations;
    }
}
