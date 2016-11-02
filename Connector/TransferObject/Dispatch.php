<?php
/**
 * Created by PhpStorm.
 * User: davidthulke
 * Date: 30.10.16
 * Time: 12:53
 */

namespace PlentyConnector\Connector\TransferObject;


class Dispatch implements TransferObjectInterface
{

    /**
     * @return string
     */
    public static function getType()
    {
        return "Dispatch";
    }

    /**
     * @param array $params
     *
     * @return self
     */
    public static function fromArray(array $params = [])
    {
        return new Dispatch();
    }
}
