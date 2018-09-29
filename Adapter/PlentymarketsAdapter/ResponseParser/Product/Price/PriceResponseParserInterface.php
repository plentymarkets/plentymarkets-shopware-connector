<?php

namespace PlentymarketsAdapter\ResponseParser\Product\Price;

use PlentyConnector\Connector\TransferObject\Product\Price\Price;

interface PriceResponseParserInterface
{
    /**
     * @param array $variation
     *
     * @return Price[]
     */
    public function parse(array $variation);
}
