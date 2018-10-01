<?php

namespace PlentymarketsAdapter\Helper;

interface VariationHelperInterface
{
    /**
     * @param array $variation
     *
     * @return array
     */
    public function getShopIdentifiers(array $variation);

    /**
     * @return array
     */
    public function getMappedPlentyClientIds();
}
