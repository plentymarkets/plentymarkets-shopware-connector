<?php

namespace ShopwareAdapter\ResponseParser\Customer;

use SystemConnector\TransferObject\Order\Customer\Customer;

interface CustomerResponseParserInterface
{
    /**
     * @return null|Customer
     */
    public function parse(array $entry);
}
