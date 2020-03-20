<?php

namespace SystemConnector\TransferObject\Media;

use SystemConnector\TransferObject\AbstractTransferObject;
use SystemConnector\TransferObject\AttributableInterface;
use SystemConnector\TransferObject\TranslatableInterface;
use SystemConnector\ValueObject\Attribute\Attribute;
use SystemConnector\ValueObject\Translation\Translation;

class Media extends AbstractTransferObject implements TranslatableInterface, AttributableInterface
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
     * link to the actual media
     *
     * @var string
     */
    private $link;

    /**
     * @var string
     */
    private $filename;

    /**
     * @var string
     */
    private $hash = '';

    /**
     * @var null|string
     */
    private $name;

    /**
     * @var null|string
     */
    private $alternateName;

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
    public function getType(): string
    {
        return self::TYPE;
    }

    /**
     * {@inheritdoc}
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

    public function getMediaCategoryIdentifier(): string
    {
        return $this->mediaCategoryIdentifier;
    }

    /**
     * @param string $mediaCategoryIdentifier
     */
    public function setMediaCategoryIdentifier($mediaCategoryIdentifier)
    {
        $this->mediaCategoryIdentifier = $mediaCategoryIdentifier;
    }

    public function getLink(): string
    {
        return $this->link;
    }

    /**
     * @param string $link
     */
    public function setLink($link)
    {
        $this->link = $link;
    }

    public function getFilename(): string
    {
        return $this->filename;
    }

    /**
     * @param string $filename
     */
    public function setFilename($filename)
    {
        $this->filename = $filename;
    }

    public function getHash(): string
    {
        return $this->hash;
    }

    /**
     * @param null|string $hash
     */
    public function setHash($hash = null)
    {
        $this->hash = $hash;
    }

    /**
     * @return null|string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param null|string $name
     */
    public function setName($name = null)
    {
        $this->name = $name;
    }

    /**
     * @return null|string
     */
    public function getAlternateName()
    {
        return $this->alternateName;
    }

    /**
     * @param null|string $alternateName
     */
    public function setAlternateName($alternateName = null)
    {
        $this->alternateName = $alternateName;
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
     * {@inheritdoc}
     */
    public function getClassProperties()
    {
        return [
            'identifier' => $this->getIdentifier(),
            'mediaCategoryIdentifier' => $this->getMediaCategoryIdentifier(),
            'link' => $this->getLink(),
            'filename' => $this->getFilename(),
            'hash' => $this->getHash(),
            'name' => $this->getName(),
            'alternateName' => $this->getAlternateName(),
            'translations' => $this->getTranslations(),
            'attributes' => $this->getAttributes(),
        ];
    }
}
