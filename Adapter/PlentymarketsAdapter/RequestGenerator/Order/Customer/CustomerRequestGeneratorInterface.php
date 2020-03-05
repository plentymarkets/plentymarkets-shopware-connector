<?php

namespace PlentymarketsAdapter\RequestGenerator\Order\Customer;

use SystemConnector\TransferObject\Order\Customer\Customer;
use SystemConnector\TransferObject\Order\Order;

interface CustomerRequestGeneratorInterface
{
    public function generate(Customer $customer, Order $order): array;
}
