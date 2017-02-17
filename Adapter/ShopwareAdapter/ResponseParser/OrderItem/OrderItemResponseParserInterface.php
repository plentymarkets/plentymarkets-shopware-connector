<?php

namespace ShopwareAdapter\ResponseParser\OrderItem;

use PlentyConnector\Connector\TransferObject\OrderItem\OrderItem;

/**
 * Interface OrderItemResponseParserInterface
 */
interface OrderItemResponseParserInterface
{
    /**
     * @param array $entry
     *
     * @return null|OrderItem
     */
    public function parse(array $entry);
}
