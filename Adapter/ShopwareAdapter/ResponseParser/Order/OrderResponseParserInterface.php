<?php

namespace ShopwareAdapter\ResponseParser\Order;

use PlentyConnector\Connector\TransferObject\TransferObjectInterface;

interface OrderResponseParserInterface
{
    /**
     * @param array $entry
     *
     * @return TransferObjectInterface[]
     */
    public function parse(array $entry);
}
