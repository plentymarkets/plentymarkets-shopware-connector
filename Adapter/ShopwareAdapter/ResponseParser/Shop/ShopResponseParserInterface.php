<?php

namespace ShopwareAdapter\ResponseParser\Shop;

use SystemConnector\TransferObject\Shop\Shop;

interface ShopResponseParserInterface
{
    /**
     * @return null|Shop
     */
    public function parse(array $entry);
}
