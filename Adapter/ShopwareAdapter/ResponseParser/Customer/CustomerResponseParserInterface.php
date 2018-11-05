<?php

namespace ShopwareAdapter\ResponseParser\Customer;

use SystemConnector\TransferObject\Order\Customer\Customer;

interface CustomerResponseParserInterface
{
    /**
     * @param array $entry
     *
     * @return null|Customer
     */
    public function parse(array $entry);
}
