<?php

namespace PlentymarketsAdapter\ResponseParser\Product;

use PlentyConnector\Connector\TransferObject\TransferObjectInterface;

/**
 * Interface ProductResponseParserInterface.
 */
interface ProductResponseParserInterface
{
    /**
     * @param array $product
     *
     * @return TransferObjectInterface[]
     */
    public function parse(array $product);
}
