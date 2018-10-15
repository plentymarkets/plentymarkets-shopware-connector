<?php

namespace PlentymarketsAdapter\RequestGenerator\Order\OrderItem;

use SystemConnector\TransferObject\Order\Order;
use SystemConnector\TransferObject\Order\OrderItem\OrderItem;

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
