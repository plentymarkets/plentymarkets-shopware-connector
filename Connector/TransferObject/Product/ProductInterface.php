<?php


namespace PlentyConnector\Connector\TransferObject\Product;

use PlentyConnector\Connector\TransferObject\TransferObjectInterface;

/**
 * Interface ProductInterface
 */
interface ProductInterface extends TransferObjectInterface
{
    /**
     * @return string
     */
    public function getName();
}
