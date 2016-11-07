<?php

namespace PlentyConnector\Connector\TransferObject\Shop;

/**
 * Class Shop
 */
class Shop implements ShopInterface
{
    /**
     * @return string
     */
    public static function getType()
    {
        return 'Shop';
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
