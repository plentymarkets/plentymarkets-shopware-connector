<?php

namespace PlentyConnector\Connector\TransferObject\Product\Property;

use Assert\Assertion;
use PlentyConnector\Connector\TransferObject\Product\Property\Value\ValueInterface;
use PlentyConnector\Connector\ValueObject\Translation\TranslationInterface;

/**
 * Class Property
 */
class Property implements PropertyInterface
{
    /**
     * @var string
     */
    private $key;

    /**
     * @var ValueInterface[]
     */
    private $values;

    /**
     * @var TranslationInterface[]
     */
    private $translations;

    /**
     * Property constructor.
     *
     * @param string $key
     * @param ValueInterface[] $values
     * @param TranslationInterface[] $translations
     */
    public function __construct($key, array $values = [], array $translations = [])
    {
        Assertion::string($key);
        Assertion::notBlank($key);
        Assertion::allIsInstanceOf($translations, ValueInterface::class);
        Assertion::allIsInstanceOf($translations, TranslationInterface::class);

        $this->key = $key;
        $this->values = $values;
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
            'translations',
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
    public function getValues()
    {
        return $this->values;
    }

    /**
     * {@inheritdoc}
     */
    public function getTranslations()
    {
        return $this->translations;
    }
}
