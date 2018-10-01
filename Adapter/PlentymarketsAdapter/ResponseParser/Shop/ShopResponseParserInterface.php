<?php

namespace PlentymarketsAdapter\ResponseParser\Shop;

use PlentyConnector\Connector\TransferObject\Shop\Shop;

interface ShopResponseParserInterface
{
    /**
     * @param array $entry
     *
     * @return null|Shop
     */
    public function parse(array $entry);
}
