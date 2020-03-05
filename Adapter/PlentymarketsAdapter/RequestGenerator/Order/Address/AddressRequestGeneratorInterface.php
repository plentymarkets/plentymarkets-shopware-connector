<?php

namespace PlentymarketsAdapter\RequestGenerator\Order\Address;

use SystemConnector\TransferObject\Order\Address\Address;
use SystemConnector\TransferObject\Order\Order;

interface AddressRequestGeneratorInterface
{
    /**
     * @param int $addressType
     */
    public function generate(Address $address, Order $order, $addressType = 0): array;
}
