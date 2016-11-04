<?php

namespace PlentyConnector\Connector\TransferObject\OrderItem;

/**
 * Class OrderItem
 */
class OrderItem implements OrderItemInterface
{
    /**
     * @var int
     */
    private $quantity;

    /**
     * @var string
     */
    private $productId;

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
        return new self();
    }
}
