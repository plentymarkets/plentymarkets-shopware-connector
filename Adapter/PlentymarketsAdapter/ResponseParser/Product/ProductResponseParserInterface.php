<?php

namespace PlentymarketsAdapter\ResponseParser\Product;

use PlentyConnector\Connector\TransferObject\TransferObjectInterface;

interface ProductResponseParserInterface
{
    /**
     * @param array $product
     *
     * @return TransferObjectInterface[]
     */
    public function parse(array $product);
}
