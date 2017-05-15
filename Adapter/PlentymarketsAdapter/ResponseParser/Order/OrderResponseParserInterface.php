<?php

namespace PlentymarketsAdapter\ResponseParser\Order;

use PlentyConnector\Connector\TransferObject\TransferObjectInterface;

/**
 * Interface OrderResponseParserInterface
 */
interface OrderResponseParserInterface
{
    /**
     * @param array $entry
     *
     * @return TransferObjectInterface[]
     */
    public function parse(array $entry);
}
