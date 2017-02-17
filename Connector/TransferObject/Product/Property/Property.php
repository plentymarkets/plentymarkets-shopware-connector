<?php

namespace PlentyConnector\Connector\TransferObject\Product\Property;

use Assert\Assertion;
use PlentyConnector\Connector\TransferObject\Product\Property\Value\Value;
use PlentyConnector\Connector\TransferObject\TranslateableInterface;
use PlentyConnector\Connector\ValueObject\AbstractValueObject;
use PlentyConnector\Connector\ValueObject\Translation\Translation;

/**
 * Class Property
 */
class Property  extends AbstractValueObject implements TranslateableInterface
{
    /**
     * @var string
     */
    private $key = '';

    /**
     * @var Value[]
     */
    private $values = [];

    /**
     * @var Translation[]
     */
    private $translations = [];

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
     * @return Value[]
     */
    public function getValues()
    {
        return $this->values;
    }

    /**
     * @param Value[] $values
     */
    public function setValues($values)
    {
        Assertion::allIsInstanceOf($values, Value::class);

        $this->values = $values;
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
