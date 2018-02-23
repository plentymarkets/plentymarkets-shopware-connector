<?php

namespace PlentymarketsAdapter\Helper;

/**
 * Interface VariationHelperInterface
 */
interface VariationHelperInterface
{
    /**
     * @param array $variation
     *
     * @return array
     */
    public function getShopIdentifiers(array $variation);
}
