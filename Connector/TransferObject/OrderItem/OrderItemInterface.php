<?php

namespace PlentyConnector\Connector\TransferObject\OrderItem;

use PlentyConnector\Connector\TransferObject\TransferObjectInterface;

/**
 * Interface OrderItemInterface
 */
interface OrderItemInterface extends TransferObjectInterface
{
    /**
     * @return int
     */
    public function getQuantity();

    /**
     * @return string
     */
    public function getProductId();

    /**
     * @return string
     */
    public function getVariationId();

    /**
     * @return string
     */
    public function getName();

    /**
     * @return float
     */
    public function getPrice();
}
