<?php

namespace PlentymarketsAdapter\ResponseParser\Product\Price;

use SystemConnector\TransferObject\Product\Price\Price;

interface PriceResponseParserInterface
{
    /**
     * @return Price[]
     */
    public function parse(array $variation): array;
}
