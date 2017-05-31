<?php

namespace PlentymarketsAdapter\RequestGenerator\Order\OrderItem;

use PlentyConnector\Connector\TransferObject\Order\Order;
use PlentyConnector\Connector\TransferObject\Order\OrderItem\OrderItem;

/**
 * Interface OrderItemRequestGeneratorInterface
 */
interface OrderItemRequestGeneratorInterface
{
    /**
     * @param OrderItem $orderItem
     * @param Order     $order
     *
     * @return array
     */
    public function generate(OrderItem $orderItem, Order $order);
}
