<?php

namespace ShopwareAdapter\ResponseParser\Address;

use PlentyConnector\Connector\TransferObject\Order\Address\Address;

interface AddressResponseParserInterface
{
    /**
     * @param array $entry
     *
     * @return null|Address
     */
    public function parse(array $entry);
}
