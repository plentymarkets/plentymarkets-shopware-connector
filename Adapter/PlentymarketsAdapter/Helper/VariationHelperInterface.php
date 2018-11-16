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

    /**
     * @param array $variations
     *
     * @return array
     */
    public function getMainVariation(array $variations);

    /**
     * @param Variation[] $variations
     * @param array       $mainVariation
     *
     * @return string
     */
    public function getMainVariationNumber(array $variations = [], array $mainVariation);
}
