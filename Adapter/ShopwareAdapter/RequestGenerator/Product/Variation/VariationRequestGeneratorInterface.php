<?php

namespace ShopwareAdapter\RequestGenerator\Product\Variation;

use SystemConnector\TransferObject\Product\Variation\Variation;

interface VariationRequestGeneratorInterface
{
    /**
     * @param Variation $variation
     *
     * @return array
     */
    public function generate(Variation $variation);
}
