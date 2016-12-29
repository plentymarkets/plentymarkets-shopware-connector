<?php

namespace PlentyConnector\Connector\TransferObject\PaymentMethod;

use Assert\Assertion;

/**
 * Class PaymentMethod.
 */
class PaymentMethod implements PaymentMethodInterface
{
    const TYPE = 'PaymentMethod';

    /**
     * @var string
     */
    private $identifer;

    /**
     * @var string
     */
    private $name;

    /**
     * PaymentMethod constructor.
     *
     * @param string $identifier
     * @param string $name
     */
    public function __construct($identifier, $name)
    {
        Assertion::uuid($identifier);
        Assertion::string($name);

        $this->identifer = $identifier;
        $this->name = $name;
    }

    /**
     * {@inheritdoc}
     */
    public static function getType()
    {
        return self::TYPE;
    }

    /**
     * {@inheritdoc}
     */
    public static function fromArray(array $params = [])
    {
        Assertion::allInArray(array_keys($params), [
            'identifier',
            'name',
        ]);

        return new self(
            $params['identifier'],
            $params['name']
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifier()
    {
        return $this->identifer;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->name;
    }
}
