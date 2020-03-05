<?php

namespace PlentyConnector\Components\Bundle\TransferObject;

use DateTimeImmutable;
use PlentyConnector\Components\Bundle\TransferObject\BundleProduct\BundleProduct;
use SystemConnector\TransferObject\AbstractTransferObject;
use SystemConnector\TransferObject\Product\Price\Price;
use SystemConnector\ValueObject\Attribute\Attribute;
use SystemConnector\ValueObject\Translation\Translation;

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
     * @var int
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
    public function getType(): string
    {
        return self::TYPE;
    }

    public function getIdentifier(): string
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

    public function isActive(): bool
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

    public function getStock(): int
    {
        return $this->stock;
    }

    /**
     * @param int $stock
     */
    public function setStock($stock)
    {
        $this->stock = $stock;
    }

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

    public function getVatRateIdentifier(): string
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

    public function setAvailableTo(DateTimeImmutable $availableTo = null)
    {
        $this->availableTo = $availableTo;
    }

    /**
     * @return BundleProduct[]
     */
    public function getBundleProducts(): array
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
    public function getAttributes(): array
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
    public function getTranslations(): array
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

    /**
     * {@inheritdoc}
     */
    public function getClassProperties()
    {
        return [
            'identifier' => $this->getIdentifier(),
            'active' => $this->isActive(),
            'productIdentifier' => $this->getProductIdentifier(),
            'name' => $this->getName(),
            'number' => $this->getNumber(),
            'position' => $this->getPosition(),
            'stock' => $this->getStock(),
            'stockLimitation' => $this->hasStockLimitation(),
            'prices' => $this->getPrices(),
            'vatRateIdentifier' => $this->getVatRateIdentifier(),
            'availableFrom' => $this->getAvailableFrom(),
            'availableTo' => $this->getAvailableTo(),
            'bundleProducts' => $this->getBundleProducts(),
            'attributes' => $this->getAttributes(),
            'translations' => $this->getTranslations(),
        ];
    }
}
