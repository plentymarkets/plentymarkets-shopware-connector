<?php

namespace ShopwareAdapter\ResponseParser\Order;

use PlentyConnector\Connector\TransferObject\Order\OrderInterface;

/**
 * Interface OrderResponseParserInterface
 */
interface OrderResponseParserInterface
{
    /**
     * @param array $entry
     *
     * @return OrderInterface|null
     */
    public function parse(array $entry);
}
