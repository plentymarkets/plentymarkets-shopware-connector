<?php

namespace PlentyConnector\Connector\TransferObject\OrderItem;

use Assert\Assertion;

/**
 * Class OrderItem
 */
class OrderItem implements OrderItemInterface
{
    /**
     * @var string
     */
    private $identifier;

    /**
     * @var int
     */
    private $quantity;

    /**
     * @var string
     */
    private $productId;

    /**
     * @var string
     */
    private $variationId;

    /**
     * @var string
     */
    private $name;

    /**
     * @var double
     */
    private $price;

    /**
     * OrderItem constructor.
     *
     * @param $identifier
     * @param $quantity
     * @param $productId
     * @param $variationId
     * @param $name
     * @param $price
     */
    public function __construct($identifier, $quantity, $productId, $variationId, $name, $price)
    {
        Assertion::uuid($identifier);
        Assertion::integer($quantity);
        Assertion::string($productId);
        Assertion::string($variationId);
        Assertion::string($name);
        Assertion::numeric($price);

        $this->identifier = $identifier;
        $this->quantity = $quantity;
        $this->productId = $productId;
        $this->variationId = $variationId;
        $this->name = $name;
        $this->price = $price;
    }

    /**
     * @return string
     */
    public static function getType()
    {
        return 'OrderItem';
    }

    /**
     * @param array $params
     *
     * @return self
     */
    public static function fromArray(array $params = [])
    {
        return new self(
            $params['identifier'],
            $params['quantity'],
            $params['productId'],
            $params['variationId'],
            $params['name'],
            $params['price']
        );
    }
}
