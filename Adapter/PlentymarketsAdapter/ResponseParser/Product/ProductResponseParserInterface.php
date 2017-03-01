<?php

namespace PlentymarketsAdapter\ResponseParser\Product;

use PlentyConnector\Connector\TransferObject\Product\Product;

/**
 * Interface ProductResponseParserInterface.
 */
interface ProductResponseParserInterface
{
    /**
     * @param array $product
     * @param array $result
     *
     * @return Product
     */
    public function parse(array $product, array &$result);
}
