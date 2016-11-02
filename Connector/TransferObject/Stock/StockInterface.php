<?php

namespace PlentyConnector\Connector\TransferObject\Stock;

use PlentyConnector\Connector\TransferObject\TransferObjectInterface;

/**
 * Interface StockInterface
 *
 * @package PlentyConnector\Connector\TransferObject
 */
interface StockInterface extends TransferObjectInterface
{
    /**
     * Identifier of the assoziated product
     *
     * @return string
     */
    public function getProductIdentifier();

    /**
     * Identifier of the assoziated variation
     *
     * @return string
     */
    public function getVariationIdentifier();

    /**
     * @return int
     */
    public function getStock();
}
