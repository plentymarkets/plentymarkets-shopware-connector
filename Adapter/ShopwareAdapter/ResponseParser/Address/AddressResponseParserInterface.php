<?php

namespace ShopwareAdapter\ResponseParser\Address;

use SystemConnector\TransferObject\Order\Address\Address;

interface AddressResponseParserInterface
{
    /**
     * @return null|Address
     */
    public function parse(array $entry);
}
