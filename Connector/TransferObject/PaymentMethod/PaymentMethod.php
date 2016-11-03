<?php

namespace PlentyConnector\Connector\TransferObject\PaymentMethod;

use PlentyConnector\Connector\TransferObject\TransferObjectType;

/**
 * Class PaymentMethod.
 */
class PaymentMethod implements PaymentMethodInterface
{
    /**
     * Identifier of the object.
     *
     * @var string
     */
    private $identifer;

    /**
     * @var string
     */
    private $name;

    /**
     * Manufacturer constructor.
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
     * @return string
     */
    public static function getType()
    {
        return TransferObjectType::PAYMENT_METHOD;
    }

    /**
     * {@inheritdoc}
     */
    public static function fromArray(array $params = [])
    {
        return new self(
            $params['identifier'],
            $params['name']
        );
    }

    /**
     * @return string
     */
    public function getIdentifier()
    {
        return $this->identifer;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }
}
