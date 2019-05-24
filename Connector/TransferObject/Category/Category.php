<?php

namespace SystemConnector\TransferObject\Category;

use SystemConnector\TransferObject\AbstractTransferObject;
use SystemConnector\TransferObject\AttributableInterface;
use SystemConnector\TransferObject\TranslatableInterface;
use SystemConnector\ValueObject\Attribute\Attribute;
use SystemConnector\ValueObject\Translation\Translation;

class Category extends AbstractTransferObject implements TranslatableInterface, AttributableInterface
{
    const TYPE = 'Category';

    /**
     * @var string
     */
    private $identifier = '';

    /**
     * @var string
     */
    private $name = '';

    /**
     * @var bool
     */
    private $active = false;

    /**
     * @var null|string
     */
    private $parentIdentifier;

    /**
     * @var array
     */
    private $shopIdentifiers = [];

    /**
     * @var array
     */
    private $imageIdentifiers = [];

    /**
     * @var int
     */
    private $position = 0;

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
    private $metaRobots = '';

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
     * {@inheritdoc}
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
     * @return null|string
     */
    public function getParentIdentifier()
    {
        return $this->parentIdentifier;
    }

    /**
     * @param string $parentIdentifier
     */
    public function setParentIdentifier($parentIdentifier)
    {
        $this->parentIdentifier = $parentIdentifier;
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
    public function setShopIdentifiers(array $shopIdentifiers = [])
    {
        $this->shopIdentifiers = $shopIdentifiers;
    }

    /**
     * @return array
     */
    public function getImageIdentifiers(): array
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
     * @return int
     */
    public function getPosition(): int
    {
        return $this->position;
    }

    /**
     * @param null|int $position
     */
    public function setPosition($position = null)
    {
        if (null === $position) {
            $position = 0;
        }

        $this->position = $position;
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
            'name' => $this->getName(),
            'active' => $this->getActive(),
            'parentIdentifiers' => $this->getParentIdentifier(),
            'shopIdentifiers' => $this->getShopIdentifiers(),
            'imageIdentifiers' => $this->getImageIdentifiers(),
            'position' => $this->getPosition(),
            'description' => $this->getDescription(),
            'longDescription' => $this->getLongDescription(),
            'metaTitle' => $this->getMetaTitle(),
            'metaDescription' => $this->getMetaDescription(),
            'metaKeywords' => $this->getMetaKeywords(),
            'metaRobots' => $this->getMetaRobots(),
            'translations' => $this->getTranslations(),
            'attributes' => $this->getAttributes(),
        ];
    }
}
