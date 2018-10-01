<?php

namespace PlentymarketsAdapter\ResponseParser\Product\Stock;

use PlentyConnector\Connector\TransferObject\TransferObjectInterface;

interface StockResponseParserInterface
{
    /**
     * @param array $variation
     *
     * @return TransferObjectInterface[]
     */
    public function parse(array $variation);
}
