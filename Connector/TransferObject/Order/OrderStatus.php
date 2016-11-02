<?php
/**
 * Created by PhpStorm.
 * User: davidthulke
 * Date: 30.10.16
 * Time: 12:56
 */

namespace PlentyConnector\Connector\TransferObject\Order;


use PlentyConnector\Connector\TransferObject\TransferObjectInterface;

class OrderStatus implements TransferObjectInterface
{
    /**
     * @return string
     */
    public static function getType()
    {
        return "OrderStatus";
    }

    /**
     * @param array $params
     *
     * @return self
     */
    public static function fromArray(array $params = [])
    {
        return new OrderStatus();
    }
}