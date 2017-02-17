<?php

namespace PlentyConnector\Connector\TransferObject\Media;

use Assert\Assertion;
use PlentyConnector\Connector\TransferObject\AbstractTransferObject;
use PlentyConnector\Connector\TransferObject\TranslateableInterface;
use PlentyConnector\Connector\ValueObject\Attribute\Attribute;
use PlentyConnector\Connector\ValueObject\Translation\Translation;

/**
 * Class Media
 */
class Media extends AbstractTransferObject implements TranslateableInterface
{
    const TYPE = 'Media';

    /**
     * @var string
     */
    private $identifier = '';

    /**
     * @var string
     */
    private $mediaCategoryIdentifier = '';

    /**
     * @var string
     */
    private $link = '';

    /**
     * @var string
     */
    private $hash = '';

    /**
     * @var string
     */
    private $name = '';

    /**
     * @var string
     */
    private $alternateName = '';

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
    public function getMediaCategoryIdentifier()
    {
        return $this->mediaCategoryIdentifier;
    }

    /**
     * @param string $mediaCategoryIdentifier
     */
    public function setMediaCategoryIdentifier($mediaCategoryIdentifier)
    {
        Assertion::uuid($mediaCategoryIdentifier);

        $this->mediaCategoryIdentifier = $mediaCategoryIdentifier;
    }

    /**
     * @return string
     */
    public function getLink()
    {
        return $this->link;
    }

    /**
     * @param string $link
     */
    public function setLink($link)
    {
        Assertion::url($link);

        $this->link = $link;
    }

    /**
     * @return string
     */
    public function getHash()
    {
        if (empty($this->hash)) {
            $this->hash = sha1_file($this->link);
        }

        return $this->hash;
    }

    /**
     * @param string $hash
     */
    public function setHash($hash)
    {
        Assertion::string($hash);

        $this->hash = $hash;
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
    public function getAlternateName()
    {
        return $this->alternateName;
    }

    /**
     * @param string $alternateName
     */
    public function setAlternateName($alternateName)
    {
        Assertion::string($alternateName);

        $this->alternateName = $alternateName;
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
