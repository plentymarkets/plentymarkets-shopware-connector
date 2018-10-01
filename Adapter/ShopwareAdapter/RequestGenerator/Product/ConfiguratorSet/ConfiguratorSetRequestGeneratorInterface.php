<?php

namespace ShopwareAdapter\RequestGenerator\Product\ConfiguratorSet;

use PlentyConnector\Connector\TransferObject\Product\Product;

interface ConfiguratorSetRequestGeneratorInterface
{
    /**
     * @param Product $product
     *
     * @return array
     */
    public function generate(Product $product);
}
