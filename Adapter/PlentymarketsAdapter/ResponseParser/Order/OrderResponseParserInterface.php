<?php

namespace PlentymarketsAdapter\ResponseParser\Order;

use PlentyConnector\Connector\TransferObject\Order\Order;

/**
 * Interface OrderResponseParserInterface
 */
interface OrderResponseParserInterface
{
    /**
     * @param array $entry
     *
     * @return null|Order
     */
    public function parse(array $entry);
}
