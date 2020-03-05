<?php

namespace PlentymarketsAdapter\RequestGenerator\Order;

use SystemConnector\TransferObject\Order\Order;

interface OrderRequestGeneratorInterface
{
    public function generate(Order $order): array;
}
