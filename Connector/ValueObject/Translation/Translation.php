<?php

namespace PlentyConnector\Connector\ValueObject\Translation;

use Assert\Assertion;
use PlentyConnector\Connector\ValueObject\AbstractValueObject;

/**
 * Class Translation
 */
class Translation extends AbstractValueObject
{
    /**
     * @var string
     */
    private $languageIdentifier;

    /**
     * @var string
     */
    private $property;

    /**
     * @var mixed
     */
    private $value;

    /**
     * return the language identifier
     *
     * @return string
     */
    public function getLanguageIdentifier()
    {
        return $this->languageIdentifier;
    }

    /**
     * @param string $languageIdentifier
     */
    public function setLanguageIdentifier($languageIdentifier)
    {
        Assertion::uuid($languageIdentifier);

        $this->languageIdentifier = $languageIdentifier;
    }

    /**
     * Get the property
     *
     * @return string
     */
    public function getProperty()
    {
        return $this->property;
    }

    /**
     * @param string $property
     */
    public function setProperty($property)
    {
        Assertion::string($property);
        Assertion::notBlank($property);

        $this->property = $property;
    }

    /**
     * Get the value
     *
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param mixed $value
     */
    public function setValue($value)
    {
        Assertion::notNull($value);

        $this->value = $value;
    }
}
