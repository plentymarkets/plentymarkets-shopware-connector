<?php

namespace PlentymarketsAdapter\ResponseParser\Product\Variation;

use SystemConnector\TransferObject\TransferObjectInterface;

interface VariationResponseParserInterface
{
    /**
     * @param array $product
     *
     * @return TransferObjectInterface[]
     */
    public function parse(array $product);
}
