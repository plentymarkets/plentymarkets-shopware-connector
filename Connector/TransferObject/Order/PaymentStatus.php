<?php
/**
 * Created by PhpStorm.
 * User: davidthulke
 * Date: 30.10.16
 * Time: 12:57
 */

namespace PlentyConnector\Connector\TransferObject\Order;


use PlentyConnector\Connector\TransferObject\TransferObjectInterface;

class PaymentStatus implements TransferObjectInterface
{
    /**
     * @return string
     */
    public static function getType()
    {
        return "PaymentStatus";
    }

    /**
     * @param array $params
     *
     * @return self
     */
    public static function fromArray(array $params = [])
    {
        return new PaymentStatus();
    }
}
