<?php

namespace PlentyConnector\Connector\TransferObject\Product\Property\Value;

use Assert\Assertion;
use PlentyConnector\Connector\ValueObject\Translation\TranslationInterface;

/**
 * Class Value
 */
class Value implements ValueInterface
{
    /**
     * @var string
     */
    private $value;

    /**
     * @var TranslationInterface[]
     */
    private $translations;

    /**
     * Value constructor.
     *
     * @param string $value
     * @param TranslationInterface[] $translations
     */
    public function __construct($value, array $translations = [])
    {
        Assertion::string($value);
        Assertion::allIsInstanceOf($translations, TranslationInterface::class);

        $this->value = $value;
        $this->translations = $translations;
    }

    /**
     * {@inheritdoc}
     */
    public static function fromArray(array $params = [])
    {
        Assertion::allInArray(array_keys($params), [
            'value',
            'translations',
        ]);

        return new self(
            $params['value'],
            $params['translations']
        );
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
