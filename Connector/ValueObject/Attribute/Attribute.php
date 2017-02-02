<?php

namespace PlentyConnector\Connector\ValueObject\Attribute;

use Assert\Assertion;
use PlentyConnector\Connector\ValueObject\Translation\TranslationInterface;

/**
 * Class Attribute
 */
class Attribute implements AttributeInterface
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
     * @var TranslationInterface[]
     */
    private $translations;

    /**
     * Attribute constructor.
     *
     * @param string $key
     * @param string $value
     * @param TranslationInterface[] $translations
     */
    public function __construct($key, $value, array $translations = [])
    {
        Assertion::string($key);
        Assertion::notBlank($key);
        Assertion::string($value);
        Assertion::allIsInstanceOf($translations, TranslationInterface::class);

        $this->key = $key;
        $this->value = $value;
        $this->translations = $translations;
    }

    /**
     * {@inheritdoc}
     */
    public static function fromArray(array $params = [])
    {
        Assertion::allInArray(array_keys($params), [
            'key',
            'value',
            'translations'
        ]);

        return new self(
            $params['key'],
            $params['value'],
            $params['translations']
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * {@inheritdoc}
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * {@inheritdoc}
     */
    public function getTranslations()
    {
        return $this->translations;
    }
}
