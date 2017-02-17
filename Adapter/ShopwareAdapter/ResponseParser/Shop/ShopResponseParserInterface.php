<?php

namespace ShopwareAdapter\ResponseParser\Shop;

use PlentyConnector\Connector\TransferObject\Shop\Shop;

/**
 * Interface ShopResponseParserInterface
 */
interface ShopResponseParserInterface
{
    /**
     * @param array $entry
     *
     * @return null|Shop
     */
    public function parse(array $entry);
}
