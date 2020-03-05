<?php

namespace ShopwareAdapter\RequestGenerator\Product\Variation;

use SystemConnector\TransferObject\Product\Variation\Variation;

interface VariationRequestGeneratorInterface
{
    public function generate(Variation $variation): array;
}
