<?php

namespace ShopwareAdapter\ResponseParser\Order;

use SystemConnector\TransferObject\TransferObjectInterface;

interface OrderResponseParserInterface
{
    /**
     * @return TransferObjectInterface[]
     */
    public function parse(array $entry): array;
}
