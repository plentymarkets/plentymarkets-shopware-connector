<?php

namespace PlentyConnector\Connector\ValueObject\Attribute;

use Assert\Assertion;
use PlentyConnector\Connector\TransferObject\TranslateableInterface;
use PlentyConnector\Connector\ValueObject\AbstractValueObject;
use PlentyConnector\Connector\ValueObject\Translation\Translation;

/**
 * Class Attribute
 */
class Attribute extends AbstractValueObject implements TranslateableInterface
{
    /**
     * @var string
     */
    private $key;

    /**
     * @var string
     */
    private $value;

    /**
     * @var Translation[]
     */
    private $translations;

    /**
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @param string $key
     */
    public function setKey($key)
    {
        Assertion::string($key);
        Assertion::notBlank($key);

        $this->key = $key;
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param string $value
     */
    public function setValue($value)
    {
        Assertion::string($value);

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
        Assertion::allIsInstanceOf($translations, Translation::class);

        $this->translations = $translations;
    }
}
