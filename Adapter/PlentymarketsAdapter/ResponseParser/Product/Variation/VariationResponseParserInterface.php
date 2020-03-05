<?php

namespace PlentymarketsAdapter\ResponseParser\Product\Variation;

use SystemConnector\TransferObject\TransferObjectInterface;

interface VariationResponseParserInterface
{
    /**
     * @return TransferObjectInterface[]
     */
    public function parse(array $product): array;
}
