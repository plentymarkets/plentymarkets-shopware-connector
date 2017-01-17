<?php

namespace PlentyConnector\Connector\ValueObject\Attribute;

use Assert\Assertion;

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
     * Attribute constructor.
     *
     * @param string $key
     * @param string $value
     */
    public function __construct($key, $value)
    {
        Assertion::string($key);
        Assertion::string($value);

        $this->key = $key;
        $this->value = $value;
    }

    /**
     * {@inheritdoc}
     */
    public static function fromArray(array $params = [])
    {
        Assertion::allInArray(array_keys($params), [
            'key',
            'value',
        ]);

        return new self(
            $params['key'],
            $params['value']
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
}
