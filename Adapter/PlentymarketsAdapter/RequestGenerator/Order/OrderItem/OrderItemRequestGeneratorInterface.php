<?php

namespace PlentymarketsAdapter\RequestGenerator\Order\OrderItem;

use SystemConnector\TransferObject\Order\Order;
use SystemConnector\TransferObject\Order\OrderItem\OrderItem;

interface OrderItemRequestGeneratorInterface
{
    public function generate(OrderItem $orderItem, Order $order): array;
}
