<?php

namespace PlentymarketsAdapter\RequestGenerator\Order;

use PlentyConnector\Connector\TransferObject\Order\Order;

interface OrderRequestGeneratorInterface
{
    /**
     * @param Order $order
     *
     * @return array
     */
    public function generate(Order $order);
}
