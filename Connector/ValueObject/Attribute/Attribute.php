<?php

namespace SystemConnector\ValueObject\Attribute;

use SystemConnector\TransferObject\TranslatableInterface;
use SystemConnector\ValueObject\AbstractValueObject;
use SystemConnector\ValueObject\Translation\Translation;

class Attribute extends AbstractValueObject implements TranslatableInterface
{
    /**
     * @var string
     */
    private $key = '';

    /**
     * @var string
     */
    private $value = '';

    /**
     * @var Translation[]
     */
    private $translations = [];

    /**
     * @return string
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * @param string $key
     */
    public function setKey($key)
    {
        $this->key = $key;
    }

    /**
     * @return string
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * @param string $value
     */
    public function setValue($value)
    {
        $this->value = $value;
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
        $this->translations = $translations;
    }

    /**
     * {@inheritdoc}
     */
    public function getClassProperties()
    {
        return [
            'key' => $this->getKey(),
            'value' => $this->getValue(),
            'translations' => $this->getTranslations(),
        ];
    }
}
