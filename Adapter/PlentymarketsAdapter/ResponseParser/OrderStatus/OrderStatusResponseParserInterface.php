<?php

namespace PlentymarketsAdapter\ResponseParser\OrderStatus;

use SystemConnector\TransferObject\OrderStatus\OrderStatus;

interface OrderStatusResponseParserInterface
{
    /**
     * @param array $entry
     *
     * @return null|OrderStatus
     */
    public function parse(array $entry);
}
