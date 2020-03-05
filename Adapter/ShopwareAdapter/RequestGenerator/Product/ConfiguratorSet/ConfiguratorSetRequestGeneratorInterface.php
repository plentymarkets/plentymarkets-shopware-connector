<?php

namespace ShopwareAdapter\RequestGenerator\Product\ConfiguratorSet;

use SystemConnector\TransferObject\Product\Product;

interface ConfiguratorSetRequestGeneratorInterface
{
    public function generate(Product $product): array;
}
