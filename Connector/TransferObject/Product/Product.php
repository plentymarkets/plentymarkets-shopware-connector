<?php

namespace PlentyConnector\Connector\TransferObject\Product;

use PlentyConnector\Connector\TransferObject\AbstractTransferObject;
use PlentyConnector\Connector\TransferObject\Product\LinkedProduct\LinkedProduct;
use PlentyConnector\Connector\TransferObject\Product\Property\Property;
use PlentyConnector\Connector\TransferObject\Product\Variation\Variation;
use PlentyConnector\Connector\TransferObject\TransferObjectInterface;
use PlentyConnector\Connector\TransferObject\TranslateableInterface;
use PlentyConnector\Connector\ValueObject\Attribute\Attribute;
use PlentyConnector\Connector\ValueObject\Translation\Translation;

/**
 * Class Product.
 */
class Product extends AbstractTransferObject implements TranslateableInterface
{
    const TYPE = 'Product';

    /**
     * Identifier of the object.
     *
     * @var string
     */
    private $identifier = '';

    /**
     * @var string
     */
    private $name = '';

    /**
     * @var string
     */
    private $number = '';

    /**
     * @var bool
     */
    private $active = false;

    /**
     * @var array
     */
    private $shopIdentifiers = [];

    /**
     * @var string
     */
    private $manufacturerIdentifier = '';

    /**
     * @var array
     */
    private $categoryIdentifiers = [];

    /**
     * @var array
     */
    private $defaultCategoryIdentifiers = [];

    /**
     * @var array
     */
    private $shippingProfileIdentifiers = [];

    /**
     * @var array
     */
    private $imageIdentifiers = [];

    /**
     * @var Variation[]
     */
    private $variations = [];

    /**
     * @var string
     */
    private $vatRateIdentifier = '';

    /**
     * @var bool
     */
    private $limitedStock = false;

    /**
     * @var string
     */
    private $description = '';

    /**
     * @var string
     */
    private $longDescription = '';

    /**
     * @var string
     */
    private $technicalDescription = '';

    /**
     * @var string
     */
    private $metaTitle = '';

    /**
     * @var string
     */
    private $metaDescription = '';

    /**
     * @var string
     */
    private $metaKeywords = '';

    /**
     * @var string
     */
    private $metaRobots = 'INDEX, FOLLOW';

    /**
     * @var LinkedProduct[]
     */
    private $linkedProducts = [];

    /**
     * @var array
     */
    private $documents = [];

    /**
     * @var Property[]
     */
    private $properties = [];

    /**
     * @var Translation[]
     */
    private $translations = [];

    /**
     * @var null|\DateTimeImmutable
     */
    private $availableFrom;

    /**
     * @var null|\DateTimeImmutable
     */
    private $availableTo;

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
     * @return array
     */
    public function getShopIdentifiers()
    {
        return $this->shopIdentifiers;
    }

    /**
     * @param array $shopIdentifiers
     */
    public function setShopIdentifiers(array $shopIdentifiers)
    {
        $this->shopIdentifiers = $shopIdentifiers;
    }

    /**
     * @return string
     */
    public function getManufacturerIdentifier()
    {
        return $this->manufacturerIdentifier;
    }

    /**
     * @param string $manufacturerIdentifier
     */
    public function setManufacturerIdentifier($manufacturerIdentifier)
    {
        $this->manufacturerIdentifier = $manufacturerIdentifier;
    }

    /**
     * @return array
     */
    public function getCategoryIdentifiers()
    {
        return $this->categoryIdentifiers;
    }

    /**
     * @param array $categoryIdentifiers
     */
    public function setCategoryIdentifiers(array $categoryIdentifiers)
    {
        $this->categoryIdentifiers = $categoryIdentifiers;
    }

    /**
     * @return array
     */
    public function getDefaultCategoryIdentifiers()
    {
        return $this->defaultCategoryIdentifiers;
    }

    /**
     * @param array $defaultCategoryIdentifiers
     */
    public function setDefaultCategoryIdentifiers(array $defaultCategoryIdentifiers)
    {
        $this->defaultCategoryIdentifiers = $defaultCategoryIdentifiers;
    }

    /**
     * @return array
     */
    public function getShippingProfileIdentifiers()
    {
        return $this->shippingProfileIdentifiers;
    }

    /**
     * @param array $shippingProfileIdentifiers
     */
    public function setShippingProfileIdentifiers(array $shippingProfileIdentifiers)
    {
        $this->shippingProfileIdentifiers = $shippingProfileIdentifiers;
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
    public function setImageIdentifiers(array $imageIdentifiers)
    {
        $this->imageIdentifiers = $imageIdentifiers;
    }

    /**
     * @return Variation[]
     */
    public function getVariations()
    {
        return $this->variations;
    }

    /**
     * @param Variation[] $variations
     */
    public function setVariations(array $variations)
    {
        $this->variations = $variations;
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
     * @return bool
     */
    public function getLimitedStock()
    {
        return $this->limitedStock;
    }

    /**
     * @param bool $limitedStock
     */
    public function setLimitedStock($limitedStock)
    {
        $this->limitedStock = $limitedStock;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @return string
     */
    public function getLongDescription()
    {
        return $this->longDescription;
    }

    /**
     * @param string $longDescription
     */
    public function setLongDescription($longDescription)
    {
        $this->longDescription = $longDescription;
    }

    /**
     * @return string
     */
    public function getTechnicalDescription()
    {
        return $this->technicalDescription;
    }

    /**
     * @param string $technicalDescription
     */
    public function setTechnicalDescription($technicalDescription)
    {
        $this->technicalDescription = $technicalDescription;
    }

    /**
     * @return string
     */
    public function getMetaTitle()
    {
        return $this->metaTitle;
    }

    /**
     * @param string $metaTitle
     */
    public function setMetaTitle($metaTitle)
    {
        $this->metaTitle = $metaTitle;
    }

    /**
     * @return string
     */
    public function getMetaDescription()
    {
        return $this->metaDescription;
    }

    /**
     * @param string $metaDescription
     */
    public function setMetaDescription($metaDescription)
    {
        $this->metaDescription = $metaDescription;
    }

    /**
     * @return string
     */
    public function getMetaKeywords()
    {
        return $this->metaKeywords;
    }

    /**
     * @param string $metaKeywords
     */
    public function setMetaKeywords($metaKeywords)
    {
        $this->metaKeywords = $metaKeywords;
    }

    /**
     * @return string
     */
    public function getMetaRobots()
    {
        return $this->metaRobots;
    }

    /**
     * @param string $metaRobots
     */
    public function setMetaRobots($metaRobots)
    {
        $this->metaRobots = $metaRobots;
    }

    /**
     * @return LinkedProduct[]
     */
    public function getLinkedProducts()
    {
        return $this->linkedProducts;
    }

    /**
     * @param LinkedProduct[] $linkedProducts
     */
    public function setLinkedProducts(array $linkedProducts)
    {
        $this->linkedProducts = $linkedProducts;
    }

    /**
     * @return array
     */
    public function getDocuments()
    {
        return $this->documents;
    }

    /**
     * @param array $documents
     */
    public function setDocuments(array $documents)
    {
        $this->documents = $documents;
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
        $this->properties = $properties;
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

    /**
     * @return null|\DateTimeImmutable
     */
    public function getAvailableFrom()
    {
        return $this->availableFrom;
    }

    /**
     * @param null|\DateTimeImmutable $availableFrom
     */
    public function setAvailableFrom(\DateTimeImmutable $availableFrom = null)
    {
        $this->availableFrom = $availableFrom;
    }

    /**
     * @return null|\DateTimeImmutable
     */
    public function getAvailableTo()
    {
        return $this->availableTo;
    }

    /**
     * @param null|\DateTimeImmutable $availableTo
     */
    public function setAvailableTo(\DateTimeImmutable $availableTo = null)
    {
        $this->availableTo = $availableTo;
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
