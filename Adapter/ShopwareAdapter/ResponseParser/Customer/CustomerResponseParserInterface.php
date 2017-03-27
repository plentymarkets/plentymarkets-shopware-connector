<?php

namespace ShopwareAdapter\ResponseParser\Customer;

use PlentyConnector\Connector\TransferObject\Order\Customer\Customer;

/**
 * Interface CustomerResponseParserInterface.
 */
interface CustomerResponseParserInterface
{
    /**
     * @param array $entry
     *
     * @return null|Customer
     */
    public function parse(array $entry);
}
