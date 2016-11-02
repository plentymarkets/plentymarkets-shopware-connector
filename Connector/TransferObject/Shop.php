<?php
/**
 * Created by PhpStorm.
 * User: jochenmanz
 * Date: 02/09/2016
 * Time: 13:21
 */

namespace PlentyConnector\Connector\TransferObject;


class Shop implements TransferObjectInterface
{

    /**
     * @return string
     */
    public static function getType()
    {
        return "Shop";
    }

    /**
     * @param array $params
     *
     * @return self
     */
    public static function fromArray(array $params = [])
    {
        return new Shop();
    }
}
