<?php

namespace ShopwareAdapter\RequestGenerator\Product\ConfiguratorSet;

use PlentyConnector\Connector\TransferObject\Product\Product;

/**
 * Interface ConfiguratorSetRequestGeneratorInterface
 */
interface ConfiguratorSetRequestGeneratorInterface
{
    /**
     * @param Product $product
     *
     * @return array
     */
    public function generate(Product $product);
}
