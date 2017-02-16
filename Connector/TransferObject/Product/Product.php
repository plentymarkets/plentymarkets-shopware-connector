<?php

namespace PlentyConnector\Connector\TransferObject\Product;

use Assert\Assertion;
use PlentyConnector\Connector\TransferObject\Product\LinkedProduct\LinkedProductInterface;
use PlentyConnector\Connector\TransferObject\Product\Property\PropertyInterface;
use PlentyConnector\Connector\TransferObject\Product\Variation\VariationInterface;
use PlentyConnector\Connector\ValueObject\Attribute\AttributeInterface;
use PlentyConnector\Connector\ValueObject\Translation\TranslationInterface;

/**
 * Class Product.
 */
class Product implements ProductInterface
{
    const TYPE = 'Product';

    /**
     * Identifier of the object.
     *
     * @var string
     */
    private $identifier;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $number;

    /**
     * @var bool
     */
    private $active;

    /**
     * @var int
     */
    private $stock;

    /**
     * @var string
     */
    private $manufacturerIdentifier;

    /**
     * @var array
     */
    private $categoryIdentifiers;

    /**
     * @var array
     */
    private $defaultCategoryIdentifiers;

    /**
     * @var array
     */
    private $shippingProfileIdentifiers;

    /**
     * @var VariationInterface[]
     */
    private $variations;

    /**
     * @var string
     */
    private $vatRateIdentifier;

    /**
     * @var string
     */
    private $description;

    /**
     * @var string
     */
    private $longDescription;

    /**
     * @var string
     */
    private $technicalDescription;

    /**
     * @var string
     */
    private $metaTitle;

    /**
     * @var string
     */
    private $metaDescription;

    /**
     * @var string
     */
    private $metaKeywords;

    /**
     * @var string
     */
    private $metaRobots;

    /**
     * @var LinkedProductInterface[]
     */
    private $linkedProducts;

    /**
     * @var array
     */
    private $documents;

    /**
     * @var PropertyInterface[]
     */
    private $properties;

    /**
     * @var TranslationInterface[]
     */
    private $translations;

    /**
     * @var AttributeInterface[]
     */
    private $attributes;

    /**
     * Product constructor.
     *
     * @param string $identifier
     * @param string $name
     * @param string $number
     * @param bool $active
     * @param int $stock
     * @param string $manufacturerIdentifier
     * @param array $categoryIdentifiers
     * @param array $defaultCategoryIdentifiers
     * @param array $shippingProfileIdentifiers
     * @param VariationInterface[] $variations
     * @param string $vatRateIdentifier
     * @param string $description
     * @param string $longDescription
     * @param string $technicalDescription
     * @param string $metaTitle
     * @param string $metaDescription
     * @param string $metaKeywords
     * @param string $metaRobots
     * @param LinkedProductInterface[] $linkedProducts
     * @param array $documents
     * @param PropertyInterface[] $properties
     * @param TranslationInterface[] $translations
     * @param AttributeInterface[] $attributes
     */
    public function __construct(
        $identifier,
        $name,
        $number,
        $active,
        $stock,
        $manufacturerIdentifier,
        array $categoryIdentifiers,
        array $defaultCategoryIdentifiers,
        array $shippingProfileIdentifiers,
        $variations,
        $vatRateIdentifier,
        $description,
        $longDescription,
        $technicalDescription,
        $metaTitle,
        $metaDescription,
        $metaKeywords,
        $metaRobots,
        array $linkedProducts = [],
        array $documents = [],
        array $properties = [],
        array $translations = [],
        array $attributes = []
    ) {
        Assertion::uuid($identifier);
        Assertion::string($name);
        Assertion::string($number);
        Assertion::boolean($active);
        Assertion::integer($stock);
        Assertion::uuid($manufacturerIdentifier);
        Assertion::allUuid($categoryIdentifiers);
        Assertion::allUuid($defaultCategoryIdentifiers);
        Assertion::allUuid($shippingProfileIdentifiers);

        Assertion::allIsInstanceOf($variations, VariationInterface::class);

        Assertion::uuid($vatRateIdentifier);

        Assertion::string($description);
        Assertion::string($longDescription);
        Assertion::string($technicalDescription);

        Assertion::string($metaTitle);
        Assertion::string($metaDescription);
        Assertion::string($metaKeywords);
        Assertion::string($metaRobots);
        Assertion::inArray($metaRobots, [
            'INDEX, FOLLOW',
            'NOINDEX, FOLLOW',
            'INDEX, NOFOLLOW',
            'NOINDEX, NOFOLLOW',
        ]);

        Assertion::allIsInstanceOf($linkedProducts, LinkedProductInterface::class);

        Assertion::allUuid($documents);

        Assertion::allIsInstanceOf($properties, PropertyInterface::class);
        Assertion::allIsInstanceOf($translations, TranslationInterface::class);
        Assertion::allIsInstanceOf($attributes, AttributeInterface::class);

        $this->identifier = $identifier;
        $this->name = $name;
        $this->number = $number;
        $this->active = $active;
        $this->stock = $stock;
        $this->manufacturerIdentifier = $manufacturerIdentifier;
        $this->categoryIdentifiers = $categoryIdentifiers;
        $this->defaultCategoryIdentifiers = $defaultCategoryIdentifiers;
        $this->shippingProfileIdentifiers = $shippingProfileIdentifiers;

        $this->variations = $variations;

        $this->vatRateIdentifier = $vatRateIdentifier;

        $this->description = $description;
        $this->longDescription = $longDescription;
        $this->technicalDescription = $technicalDescription;

        $this->metaTitle = $metaTitle;
        $this->metaDescription = $metaDescription;
        $this->metaKeywords = $metaKeywords;
        $this->metaRobots = $metaRobots;

        $this->linkedProducts = $linkedProducts;

        $this->documents = $documents;

        $this->properties = $properties;
        $this->translations = $translations;
        $this->attributes = $attributes;
    }

    /**
     * {@inheritdoc}
     */
    public static function fromArray(array $params = [])
    {
        Assertion::allInArray(array_keys($params), [
            'identifier',
            'name',
            'number',
            'active',
            'stock',
            'manufacturerIdentifier',
            'categoryIdentifiers',
            'defaultCategoryIdentifiers',
            'shippingProfileIdentifiers',

            'variations',

            'vatRateIdentifier',

            'description',
            'longDescription',
            'technicalDescription',

            'metaTitle',
            'metaDescription',
            'metaKeywords',
            'metaRobots',

            'documents',

            'properties',
            'translations',
            'attributes',
        ]);

        return new self(
            $params['identifier'],
            $params['name'],
            $params['number'],
            $params['active'],
            $params['stock'],
            $params['manufacturerIdentifier'],
            $params['categoryIdentifiers'],
            $params['defaultCategoryIdentifiers'],
            $params['shippingProfileIdentifiers'],

            $params['variations'],

            $params['vatRateIdentifier'],

            $params['description'],
            $params['longDescription'],
            $params['technicalDescription'],

            $params['metaTitle'],
            $params['metaDescription'],
            $params['metaKeywords'],
            $params['metaRobots'],

            $params['documents'],

            $params['properties'],
            $params['translations'],
            $params['attributes']
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return self::TYPE;
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function getNumber()
    {
        return $this->number;
    }

    /**
     * {@inheritdoc}
     */
    public function isActive()
    {
        return $this->active;
    }

    /**
     * {@inheritdoc}
     */
    public function getStock()
    {
        return $this->stock;
    }

    /**
     * {@inheritdoc}
     */
    public function getManufacturerIdentifier()
    {
        return $this->manufacturerIdentifier;
    }

    /**
     * {@inheritdoc}
     */
    public function getCategoryIdentifiers()
    {
        return $this->categoryIdentifiers;
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultCategoryIdentifiers()
    {
        return $this->defaultCategoryIdentifiers;
    }

    /**
     * {@inheritdoc}
     */
    public function getShippingProfileIdentifiers()
    {
        return $this->shippingProfileIdentifiers;
    }

    /**
     * {@inheritdoc}
     */
    public function getVariations()
    {
        return $this->variations;
    }

    /**
     * {@inheritdoc}
     */
    public function getVatRateIdentifier()
    {
        return $this->vatRateIdentifier;
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * {@inheritdoc}
     */
    public function getLongDescription()
    {
        return $this->longDescription;
    }

    /**
     * {@inheritdoc}
     */
    public function getTechnicalDescription()
    {
        return $this->technicalDescription;
    }

    /**
     * {@inheritdoc}
     */
    public function getMetaTitle()
    {
        return $this->metaTitle;
    }

    /**
     * {@inheritdoc}
     */
    public function getMetaDescription()
    {
        return $this->metaDescription;
    }

    /**
     * {@inheritdoc}
     */
    public function getMetaKeywords()
    {
        return $this->metaKeywords;
    }

    /**
     * {@inheritdoc}
     */
    public function getMetaRobots()
    {
        return $this->metaRobots;
    }

    /**
     * {@inheritdoc}
     */
    public function getLinkedProducts()
    {
        return $this->linkedProducts;
    }

    /**
     * {@inheritdoc}
     */
    public function getDocuments()
    {
        return $this->documents;
    }

    /**
     * {@inheritdoc}
     */
    public function getProperties()
    {
        return $this->properties;
    }

    /**
     * {@inheritdoc}
     */
    public function getTranslations()
    {
        return $this->translations;
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributes()
    {
        return $this->attributes;
    }
}
