<?php

namespace SystemConnector\TransferObject\Product\Image;

use SystemConnector\TransferObject\TranslatableInterface;
use SystemConnector\ValueObject\AbstractValueObject;
use SystemConnector\ValueObject\Translation\Translation;

class Image extends AbstractValueObject implements TranslatableInterface
{
    /**
     * @var string
     */
    private $mediaIdentifier;

    /**
     * @var array
     */
    private $shopIdentifiers = [];

    /**
     * @var int
     */
    private $position = 0;

    /**
     * @var string
     */
    private $name;

    /**
     * @var Translation[]
     */
    private $translations = [];

    /**
     * @return mixed
     */
    public function getMediaIdentifier()
    {
        return $this->mediaIdentifier;
    }

    /**
     * @param mixed $mediaIdentifier
     */
    public function setMediaIdentifier($mediaIdentifier)
    {
        $this->mediaIdentifier = $mediaIdentifier;
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
    public function setShopIdentifiers($shopIdentifiers)
    {
        $this->shopIdentifiers = $shopIdentifiers;
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
     * {@inheritdoc}
     */
    public function getClassProperties()
    {
        return [
            'mediaIdentifier' => $this->getMediaIdentifier(),
            'shopIdentifiers' => $this->getShopIdentifiers(),
            'position' => $this->getPosition(),
        ];
    }
}
