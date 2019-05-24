<?php

namespace PlentymarketsAdapter\RequestGenerator\Order;

use SystemConnector\TransferObject\Order\Order;

interface OrderRequestGeneratorInterface
{
    /**
     * @param Order $order
     *
     * @return array
     */
    public function generate(Order $order): array;
}
