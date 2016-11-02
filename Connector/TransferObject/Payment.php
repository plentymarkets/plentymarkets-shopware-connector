<?php
/**
 * Created by PhpStorm.
 * User: davidthulke
 * Date: 30.10.16
 * Time: 12:52
 */

namespace PlentyConnector\Connector\TransferObject;


class Payment implements TransferObjectInterface
{

    /**
     * @return string
     */
    public static function getType()
    {
        return "Payment";
    }

    /**
     * @param array $params
     *
     * @return self
     */
    public static function fromArray(array $params = [])
    {
        return new Payment();
    }
}
