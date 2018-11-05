<?php

namespace PlentymarketsAdapter\ResponseParser\Order;

use SystemConnector\TransferObject\TransferObjectInterface;

interface OrderResponseParserInterface
{
    /**
     * @param array $entry
     *
     * @return TransferObjectInterface[]
     */
    public function parse(array $entry);
}
