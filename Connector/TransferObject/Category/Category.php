<?php

namespace PlentyConnector\Connector\TransferObject\Category;

use Assert\Assertion;
use PlentyConnector\Connector\ValueObject\Attribute\AttributeInterface;
use PlentyConnector\Connector\ValueObject\Translation\TranslationInterface;

/**
 * Class Category
 */
class Category implements CategoryInterface
{
    const TYPE = 'Category';

    /**
     * @var string
     */
    private $identifier;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string|null
     */
    private $parentIdentifier;

    /**
     * @var string
     */
    private $shopIdentifier;

    /**
     * @var array
     */
    private $imageIdentifiers;

    /**
     * @var int
     */
    private $position;

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
     * @var TranslationInterface[]
     */
    private $translations;

    /**
     * @var AttributeInterface[]
     */
    private $attributes;

    /**
     * Category constructor.
     *
     * @param string $identifier
     * @param string $name
     * @param string|null $parentIdentifier
     * @param string $shopIdentifier
     * @param array $imageIdentifiers
     * @param int $position
     * @param string $description
     * @param string $longDescription
     * @param string $metaTitle
     * @param string $metaDescription
     * @param string $metaKeywords
     * @param string $metaRobots
     * @param TranslationInterface[] $translations
     * @param AttributeInterface[] $attributes
     */
    public function __construct(
        $identifier,
        $name,
        $parentIdentifier,
        $shopIdentifier,
        array $imageIdentifiers,
        $position,
        $description,
        $longDescription,
        $metaTitle,
        $metaDescription,
        $metaKeywords,
        $metaRobots,
        array $translations = [],
        array $attributes = []
    ) {
        Assertion::uuid($identifier);

        Assertion::string($name);
        Assertion::notBlank($name);

        Assertion::nullOrUuid($parentIdentifier);
        Assertion::uuid($shopIdentifier);
        Assertion::allUuid($imageIdentifiers);

        Assertion::integer($position);
        Assertion::greaterOrEqualThan($position, 0);

        Assertion::string($description);
        Assertion::string($longDescription);

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

        Assertion::allIsInstanceOf($translations, TranslationInterface::class);
        Assertion::allIsInstanceOf($attributes, AttributeInterface::class);

        $this->identifier = $identifier;

        $this->name = $name;
        $this->parentIdentifier = $parentIdentifier;
        $this->shopIdentifier = $shopIdentifier;
        $this->imageIdentifiers = $imageIdentifiers;

        $this->position = $position;

        $this->description = $description;
        $this->longDescription = $longDescription;

        $this->metaTitle = $metaTitle;
        $this->metaDescription = $metaDescription;
        $this->metaKeywords = $metaKeywords;
        $this->metaRobots = $metaRobots;

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
            'parentIdentifier',
            'shopIdentifier',
            'imageIdentifiers',
            'position',
            'description',
            'longDescription',
            'metaTitle',
            'metaDescription',
            'metaKeywords',
            'metaRobots',
            'translations',
            'attributes',
        ]);

        return new self(
            $params['identifier'],
            $params['name'],
            $params['parentIdentifier'],
            $params['shopIdentifier'],
            $params['imageIdentifiers'],
            $params['position'],
            $params['description'],
            $params['longDescription'],
            $params['metaTitle'],
            $params['metaDescription'],
            $params['metaKeywords'],
            $params['metaRobots'],
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
    public function getParentIdentifier()
    {
        return $this->parentIdentifier;
    }

    /**
     * {@inheritdoc}
     */
    public function getShopIdentifier()
    {
        return $this->shopIdentifier;
    }

    /**
     * {@inheritdoc}
     */
    public function getImageIdentifiers()
    {
        return $this->imageIdentifiers;
    }

    /**
     * {@inheritdoc}
     */
    public function getPosition()
    {
        return $this->position;
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
