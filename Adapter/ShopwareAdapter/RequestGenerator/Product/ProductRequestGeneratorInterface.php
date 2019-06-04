<?php

namespace ShopwareAdapter\RequestGenerator\Product;

use SystemConnector\TransferObject\Product\Product;

interface ProductRequestGeneratorInterface
{
    /**
     * @param Product $product
     *
     * @return array
     */
    public function generate(Product $product): array;
}
