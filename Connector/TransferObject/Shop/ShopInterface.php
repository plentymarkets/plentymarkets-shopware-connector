<?php

namespace PlentyConnector\Connector\TransferObject\Shop;

use PlentyConnector\Connector\TransferObject\TransferObjectInterface;

/**
 * Interface ShopInterface
 */
interface ShopInterface extends TransferObjectInterface
{
    /**
     * @return string
     */
    public function getName();
}
