<?php

namespace ShopwareAdapter\RequestGenerator\Product\Variation;

use PlentyConnector\Connector\TransferObject\Product\Variation\Variation;

/**
 * Interface VariationRequestGeneratorInterface
 */
interface VariationRequestGeneratorInterface
{
    /**
     * @param Variation $variation
     *
     * @return array
     */
    public function generate(Variation $variation);
}
