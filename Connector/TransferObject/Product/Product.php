<?php

namespace SystemConnector\TransferObject\Product;

use DateTimeImmutable;
use SystemConnector\TransferObject\AbstractTransferObject;
use SystemConnector\TransferObject\AttributableInterface;
use SystemConnector\TransferObject\Product\Badge\Badge;
use SystemConnector\TransferObject\Product\Image\Image;
use SystemConnector\TransferObject\Product\LinkedProduct\LinkedProduct;
use SystemConnector\TransferObject\Product\Property\Property;
use SystemConnector\TransferObject\TranslatableInterface;
use SystemConnector\ValueObject\Attribute\Attribute;
use SystemConnector\ValueObject\Translation\Translation;

class Product extends AbstractTransferObject implements TranslatableInterface, AttributableInterface
{
    const TYPE = 'Product';
    const MULTIPACK = 'multiPack';

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
     * @var Image[]
     */
    private $images = [];

    /**
     * @var string
     */
    private $vatRateIdentifier = '';

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
     * @var null|DateTimeImmutable
     */
    private $availableFrom;

    /**
     * @var null|DateTimeImmutable
     */
    private $availableTo;

    /**
     * @var null|DateTimeImmutable
     */
    private $createdAt;

    /**
     * @var Attribute[]
     */
    private $attributes = [];

    /**
     * @var Property[]
     */
    private $variantConfiguration;

    /**
     * @var Badge[]
     */
    private $badges = [];

    /**
     * {@inheritdoc}
     */
    public function getType(): string
    {
        return self::TYPE;
    }

    /**
     * @return string
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
     * @return bool
     */
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

    /**
     * @return array
     */
    public function getShopIdentifiers(): array
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
    public function getManufacturerIdentifier(): string
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
    public function getCategoryIdentifiers(): array
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
    public function getDefaultCategoryIdentifiers(): array
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
    public function getShippingProfileIdentifiers(): array
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
     * @return string
     */
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
     * @return string
     */
    public function getDescription(): string
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
    public function getLongDescription(): string
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
    public function getMetaTitle(): string
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
    public function getMetaDescription(): string
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
    public function getMetaKeywords(): string
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
    public function getMetaRobots(): string
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
    public function getLinkedProducts(): array
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
    public function getDocuments(): array
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
    public function getProperties(): array
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
     * @return null|DateTimeImmutable
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param null|DateTimeImmutable $createdAt
     */
    public function setCreatedAt(DateTimeImmutable $createdAt = null)
    {
        $this->createdAt = $createdAt;
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
    public function setAttributes(array $attributes)
    {
        $this->attributes = $attributes;
    }

    /**
     * @return Property[]
     */
    public function getVariantConfiguration(): array
    {
        return $this->variantConfiguration;
    }

    /**
     * @param Property[] $variantConfiguration
     */
    public function setVariantConfiguration(array $variantConfiguration = [])
    {
        $this->variantConfiguration = $variantConfiguration;
    }

    /**
     * @return Badge[]
     */
    public function getBadges(): array
    {
        return $this->badges;
    }

    /**
     * @param Badge[] $badges
     */
    public function setBadges(array $badges = [])
    {
        $this->badges = $badges;
    }

    /**
     * {@inheritdoc}
     */
    public function getClassProperties()
    {
        return [
            'identifier' => $this->getIdentifier(),
            'name' => $this->getName(),
            'active' => $this->isActive(),
            'shopIdentifiers' => $this->getShopIdentifiers(),
            'manufacturerIdentifier' => $this->getManufacturerIdentifier(),
            'categoryIdentifiers' => $this->getCategoryIdentifiers(),
            'defaultCategoryIdentifiers' => $this->getDefaultCategoryIdentifiers(),
            'shippingProfileIdentifiers' => $this->getShippingProfileIdentifiers(),
            'vatRateIdentifier' => $this->getVatRateIdentifier(),
            'description' => $this->getDescription(),
            'longDescription' => $this->getLongDescription(),
            'metaTitle' => $this->getMetaTitle(),
            'metaDescription' => $this->getMetaDescription(),
            'metaKeywords' => $this->getMetaKeywords(),
            'metaRobots' => $this->getMetaRobots(),
            'linkedProducts' => $this->getLinkedProducts(),
            'documents' => $this->getDocuments(),
            'properties' => $this->getProperties(),
            'availableFrom' => $this->getAvailableFrom(),
            'availableTo' => $this->getAvailableTo(),
            'variantConfiguration' => $this->getVariantConfiguration(),
            'badges' => $this->getBadges(),
            'translations' => $this->getTranslations(),
            'attributes' => $this->getAttributes(),
        ];
    }
}
