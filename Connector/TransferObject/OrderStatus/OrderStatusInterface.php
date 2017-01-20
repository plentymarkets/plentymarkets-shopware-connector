<?php

namespace PlentyConnector\Connector\TransferObject\OrderStatus;

use PlentyConnector\Connector\TransferObject\TransferObjectInterface;

/**
 * Interface OrderStatusInterface
 */
interface OrderStatusInterface extends TransferObjectInterface
{
    /**
     * @return string
     */
    public function getName();
}
