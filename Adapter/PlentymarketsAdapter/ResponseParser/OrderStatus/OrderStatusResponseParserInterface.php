<?php

namespace PlentymarketsAdapter\ResponseParser\OrderStatus;

use PlentyConnector\Connector\TransferObject\OrderStatus\OrderStatus;

/**
 * Interface OrderStatusResponseParserInterface
 */
interface OrderStatusResponseParserInterface
{
    /**
     * @param array $entry
     *
     * @return null|OrderStatus
     */
    public function parse(array $entry);
}
