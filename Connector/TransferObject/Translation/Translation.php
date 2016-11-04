<?php

namespace PlentyConnector\Connector\TransferObject\Translation;

use Assert\Assertion;
use PlentyConnector\Connector\TransferObject\TransferObjectType;

/**
 * Class Translation
 */
class Translation implements TranslationInterface
{
    /**
     * @var string
     */
    private $locale;

    /**
     * @var string
     */
    private $property;

    /**
     * @var string
     */
    private $value;

    /**
     * {@inheritdoc}
     */
    public static function getType()
    {
        return TransferObjectType::TRANSLATION;
    }

    /**
     * Translation constructor.
     *
     * @param $locale
     * @param $property
     * @param $value
     */
    public function __construct($locale, $property, $value)
    {
        Assertion::string($locale);
        Assertion::string($property);
        Assertion::string($value);
    }

    /**
     * {@inheritdoc}
     */
    public static function fromArray(array $params = [])
    {
        Assertion::allInArray(array_keys($params), [
            'locale',
            'property',
            'value',
        ]);

        return new self(
            $params['locale'],
            $params['property'],
            $params['value']
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getLocale()
    {
        return $this->locale;
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
