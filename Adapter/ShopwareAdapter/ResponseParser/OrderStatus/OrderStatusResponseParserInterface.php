<?php

namespace ShopwareAdapter\ResponseParser\OrderStatus;

use SystemConnector\TransferObject\OrderStatus\OrderStatus;

interface OrderStatusResponseParserInterface
{
    /**
     * @return null|OrderStatus
     */
    public function parse(array $entry);
}
