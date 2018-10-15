<?php

namespace ShopwareAdapter\ResponseParser\Address;

use SystemConnector\TransferObject\Order\Address\Address;

interface AddressResponseParserInterface
{
    /**
     * @param array $entry
     *
     * @return null|Address
     */
    public function parse(array $entry);
}
