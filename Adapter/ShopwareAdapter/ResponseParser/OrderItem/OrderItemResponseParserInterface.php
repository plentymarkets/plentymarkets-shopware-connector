<?php

namespace ShopwareAdapter\ResponseParser\OrderItem;

use SystemConnector\TransferObject\Order\OrderItem\OrderItem;

interface OrderItemResponseParserInterface
{
    /**
     * @param bool $taxFree
     *
     * @return null|OrderItem
     */
    public function parse(array $entry, $taxFree = false);
}
