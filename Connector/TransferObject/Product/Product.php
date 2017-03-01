<?php

namespace PlentyConnector\Connector\TransferObject\Product;

use Assert\Assertion;
use PlentyConnector\Connector\TransferObject\AbstractTransferObject;
use PlentyConnector\Connector\TransferObject\Product\LinkedProduct\LinkedProduct;
use PlentyConnector\Connector\TransferObject\Product\Property\Property;
use PlentyConnector\Connector\TransferObject\Product\Variation\Variation;
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
    private $metaRobots = '';

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
        Assertion::notBlank($this->identifier);

        return $this->identifier;
    }

    /**
     * @param string $identifier
     */
    public function setIdentifier($identifier)
    {
        Assertion::uuid($identifier);

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
        Assertion::string($name);

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
        Assertion::string($number);

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
        Assertion::boolean($active);

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
    public function setShopIdentifiers($shopIdentifiers)
    {
        Assertion::allUuid($shopIdentifiers);

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
        Assertion::uuid($manufacturerIdentifier);

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
    public function setCategoryIdentifiers($categoryIdentifiers)
    {
        Assertion::allUuid($categoryIdentifiers);

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
    public function setDefaultCategoryIdentifiers($defaultCategoryIdentifiers)
    {
        Assertion::allUuid($defaultCategoryIdentifiers);

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
    public function setShippingProfileIdentifiers($shippingProfileIdentifiers)
    {
        Assertion::allUuid($shippingProfileIdentifiers);

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
    public function setImageIdentifiers($imageIdentifiers)
    {
        Assertion::allUuid($imageIdentifiers);

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
    public function setVariations($variations)
    {
        Assertion::allIsInstanceOf($variations, Variation::class);

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
        Assertion::uuid($vatRateIdentifier);

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
        Assertion::boolean($limitedStock);

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
        Assertion::string($description);

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
        Assertion::string($longDescription);

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
        Assertion::string($technicalDescription);

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
        Assertion::string($metaTitle);

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
        Assertion::string($metaDescription);

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
        Assertion::string($metaKeywords);

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
        Assertion::string($metaRobots);
        Assertion::inArray($metaRobots, [
            'INDEX, FOLLOW',
            'NOINDEX, FOLLOW',
            'INDEX, NOFOLLOW',
            'NOINDEX, NOFOLLOW',
        ]);

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
    public function setLinkedProducts($linkedProducts)
    {
        Assertion::allIsInstanceOf($linkedProducts, LinkedProduct::class);

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
    public function setDocuments($documents)
    {
        Assertion::allUuid($documents);

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
        Assertion::allIsInstanceOf($properties, Property::class);

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
    public function setTranslations($translations)
    {
        Assertion::allIsInstanceOf($translations, Translation::class);

        $this->translations = $translations;
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
}
