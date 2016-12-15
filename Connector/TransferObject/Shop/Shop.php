<?php

namespace PlentyConnector\Connector\TransferObject\Shop;

use Assert\Assertion;
use PlentyConnector\Connector\TransferObject\TransferObjectType;

/**
 * Class Shop
 */
class Shop implements ShopInterface
{
    /**
     * @var string
     */
    private $identifier;

    /**
     * @var string
     */
    private $name;

    /**
     * OrderStatus constructor.
     *
     * @param $identifier
     */
    public function __construct($identifier, $name)
    {
        Assertion::uuid($identifier);
        Assertion::string($name);

        $this->identifier = $identifier;
        $this->name = $name;
    }

    /**
     * @return string
     */
    public static function getType()
    {
        return TransferObjectType::SHOP;
    }

    /**
     * @param array $params
     *
     * @return self
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
     * @return string
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }
}
