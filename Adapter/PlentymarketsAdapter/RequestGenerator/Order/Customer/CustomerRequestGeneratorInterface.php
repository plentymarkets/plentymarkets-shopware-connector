<?php

namespace PlentymarketsAdapter\RequestGenerator\Order\Customer;

use SystemConnector\TransferObject\Order\Customer\Customer;
use SystemConnector\TransferObject\Order\Order;

interface CustomerRequestGeneratorInterface
{
    /**
     * @param Customer $customer
     * @param Order    $order
     *
     * @return array
     */
    public function generate(Customer $customer, Order $order): array;
}
