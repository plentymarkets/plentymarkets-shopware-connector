<?php

namespace PlentymarketsAdapter\ResponseParser\Product\Stock;

use SystemConnector\TransferObject\Product\Stock\Stock;

interface StockResponseParserInterface
{
    /**
     * @return null|Stock
     */
    public function parse(array $variation);
}
