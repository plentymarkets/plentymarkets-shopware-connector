<?php
/**
 * Created by PhpStorm.
 * User: davidthulke
 * Date: 30.10.16
 * Time: 13:03
 */

namespace PlentyConnector\Connector\TransferObject\Order;


use PlentyConnector\Connector\TransferObject\Product;
use PlentyConnector\Connector\TransferObject\TransferObjectInterface;

class OrderItem implements TransferObjectInterface
{
    // Missing Properties
    // - Price, Currency and exchange rate
    // - Store here or copy from individual items?
    // - What happen if the price changes?
    // - Should the product name / id be stored here or taken from the product?
    //   What happens if these change?

    /**
     * @var int
     */
    private $quantity;

    /**
     * @var Product
     */
    private $product;

    /**
     * @return string
     */
    public static function getType()
    {
        return "OrderItem";
    }

    /**
     * @param array $params
     *
     * @return self
     */
    public static function fromArray(array $params = [])
    {
        return new OrderItem();
    }
}
