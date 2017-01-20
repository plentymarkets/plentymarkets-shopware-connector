<?php

namespace ShopwareAdapter\ResponseParser\OrderItem;

use PlentyConnector\Connector\TransferObject\OrderItem\OrderItemInterface;

/**
 * Interface OrderItemResponseParserInterface
 */
interface OrderItemResponseParserInterface
{
    /**
     * @param array $entry
     *
     * @return OrderItemInterface|null
     */
    public function parse(array $entry);
}
