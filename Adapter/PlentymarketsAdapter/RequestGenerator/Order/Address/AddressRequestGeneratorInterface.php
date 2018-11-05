<?php

namespace PlentymarketsAdapter\RequestGenerator\Order\Address;

use SystemConnector\TransferObject\Order\Address\Address;
use SystemConnector\TransferObject\Order\Order;

interface AddressRequestGeneratorInterface
{
    /**
     * @param Address $address
     * @param Order   $order
     * @param int     $addressType
     *
     * @return array
     */
    public function generate(Address $address, Order $order, $addressType = 0);
}
