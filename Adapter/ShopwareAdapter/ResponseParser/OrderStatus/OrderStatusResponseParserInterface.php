<?php

namespace ShopwareAdapter\ResponseParser\OrderStatus;

use PlentyConnector\Connector\TransferObject\OrderStatus\OrderStatusInterface;

/**
 * Interface OrderStatusResponseParserInterface
 */
interface OrderStatusResponseParserInterface
{
    /**
     * @param array $entry
     *
     * @return OrderStatusInterface|null
     */
    public function parse(array $entry);
}
