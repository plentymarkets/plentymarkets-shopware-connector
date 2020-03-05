<?php

namespace ShopwareAdapter\RequestGenerator\Product;

use SystemConnector\TransferObject\Product\Product;

interface ProductRequestGeneratorInterface
{
    public function generate(Product $product): array;
}
