<?php

namespace PlentymarketsAdapter\ResponseParser\Product\Variation;

use PlentyConnector\Connector\TransferObject\TransferObjectInterface;

interface VariationResponseParserInterface
{
    /**
     * @param array $product
     *
     * @return TransferObjectInterface[]
     */
    public function parse(array $product);
}
