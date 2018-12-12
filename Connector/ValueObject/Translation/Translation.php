<?php

namespace SystemConnector\ValueObject\Translation;

use SystemConnector\ValueObject\AbstractValueObject;

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
        $this->languageIdentifier = $languageIdentifier;
    }

    /**
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
        $this->property = $property;
    }

    /**
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
        $this->value = $value;
    }

    /**
     * {@inheritdoc}
     */
    public function getClassProperties()
    {
        return [
            'languageIdentifier' => $this->getLanguageIdentifier(),
            'property' => $this->getProperty(),
            'value' => $this->getValue(),
        ];
    }
}
