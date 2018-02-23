<?php

namespace PlentymarketsAdapter\Helper;

/**
 * Interface ShopIdentifierHelperInterface
 */
interface ShopIdentifierHelperInterface
{
    /**
     * @param array $variation
     *
     * @return array
     */
    public function getShopIdentifiers(array $variation);
}
