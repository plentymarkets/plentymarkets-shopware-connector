<?php

namespace PlentyConnector\Connector\ValueObject\Translation;

use Assert\Assertion;

/**
 * Class Translation
 */
class Translation implements TranslationInterface
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
     * @var string
     */
    private $value;

    /**
     * Translation constructor.
     *
     * @param $languageIdentifier
     * @param $property
     * @param $value
     */
    public function __construct($languageIdentifier, $property, $value)
    {
        Assertion::uuid($languageIdentifier);
        Assertion::string($property);
        Assertion::string($value);

        $this->languageIdentifier = $languageIdentifier;
        $this->property = $property;
        $this->value = $value;
    }

    /**
     * {@inheritdoc}
     */
    public static function fromArray(array $params = [])
    {
        Assertion::allInArray(array_keys($params), [
            'languageIdentifier',
            'property',
            'value',
        ]);

        return new self(
            $params['languageIdentifier'],
            $params['property'],
            $params['value']
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getLanguageIdentifier()
    {
        return $this->languageIdentifier;
    }

    /**
     * {@inheritdoc}
     */
    public function getProperty()
    {
        return $this->property;
    }

    /**
     * {@inheritdoc}
     */
    public function getValue()
    {
        return $this->value;
    }
}
