<?php

namespace ShopwareAdapter\ResponseParser\Shop;

use PlentyConnector\Connector\TransferObject\Shop\ShopInterface;

/**
 * Interface ShopResponseParserInterface
 */
interface ShopResponseParserInterface
{
    /**
     * @param array $entry
     *
     * @return ShopInterface|null
     */
    public function parse(array $entry);
}
