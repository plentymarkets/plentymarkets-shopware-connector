<?php

namespace PlentymarketsAdapter\RequestGenerator\Order\Customer;

use PlentyConnector\Connector\TransferObject\Order\Customer\Customer;
use PlentyConnector\Connector\TransferObject\Order\Order;

/**
 * Interface CustomerRequestGeneratorInterface
 */
interface CustomerRequestGeneratorInterface
{
    /**
     * @param Customer $customer
     * @param Order    $order
     *
     * @return array
     */
    public function generate(Customer $customer, Order $order);
}
