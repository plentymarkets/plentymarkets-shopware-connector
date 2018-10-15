<?php

namespace PlentymarketsAdapter\ResponseParser\Product\Stock;

use SystemConnector\TransferObject\Product\Stock\Stock;

interface StockResponseParserInterface
{
    /**
     * @param array $variation
     *
     * @return Stock
     */
    public function parse(array $variation);
}
