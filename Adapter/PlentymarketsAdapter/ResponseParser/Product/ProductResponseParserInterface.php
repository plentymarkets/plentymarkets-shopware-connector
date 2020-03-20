<?php

namespace PlentymarketsAdapter\ResponseParser\Product;

use SystemConnector\TransferObject\TransferObjectInterface;

interface ProductResponseParserInterface
{
    /**
     * @return TransferObjectInterface[]
     */
    public function parse(array $product): array;
}
