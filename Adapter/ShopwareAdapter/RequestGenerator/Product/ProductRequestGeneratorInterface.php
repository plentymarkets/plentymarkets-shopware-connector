<?php

namespace ShopwareAdapter\RequestGenerator\Product;

use PlentyConnector\Connector\TransferObject\Product\Product;

/**
 * Interface ProductRequestGeneratorInterface
 */
interface ProductRequestGeneratorInterface
{
    /**
     * @param Product $product
     *
     * @return array
     */
    public function generate(Product $product);
}
