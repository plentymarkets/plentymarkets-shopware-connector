<?php

namespace PlentymarketsAdapter\Helper;

interface VariationHelperInterface
{
    /**
     * @param array $variation
     *
     * @return array
     */
    public function getShopIdentifiers(array $variation): array;

    /**
     * @return array
     */
    public function getMappedPlentyClientIds(): array;

    /**
     * @param array $variations
     *
     * @return array
     */
    public function getMainVariation(array $variations): array;

    /**
     * @param array $mainVariation
     * @param array $variations
     *
     * @return string
     */
    public function getMainVariationNumber(array $mainVariation, array $variations = []): string;
}
