<?php

namespace PlentymarketsAdapter\ResponseParser\Product\Price;

use SystemConnector\TransferObject\Product\Price\Price;

interface PriceResponseParserInterface
{
    /**
     * @param array $variation
     *
     * @return Price[]
     */
    public function parse(array $variation): array;
}
