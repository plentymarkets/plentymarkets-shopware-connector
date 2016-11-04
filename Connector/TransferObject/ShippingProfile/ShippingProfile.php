<?php

namespace PlentyConnector\Connector\TransferObject\PaymentMethod;

use PlentyConnector\Connector\TransferObject\TransferObjectType;

/**
 * Class ShippingProfile.
 */
class ShippingProfile implements ShippingProfileInterface
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
     * {@inheritdoc}
     */
    public static function getType()
    {
        return TransferObjectType::SHIPPING_PROFILE;
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
