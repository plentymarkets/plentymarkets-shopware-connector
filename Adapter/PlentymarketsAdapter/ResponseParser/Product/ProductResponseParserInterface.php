<?php

namespace PlentymarketsAdapter\ResponseParser\Product;

/**
 * Interface ProductResponseParserInterface.
 */
interface ProductResponseParserInterface
{
    /**
     * @param array $variations
     *
     * @return array
     */
    public function getMainVariation(array $variations);

    /**
     * @param $mainVariation
     *
     * @return array
     */
    public function getPrices($mainVariation);

    /**
     * @param array $product
     * @param array $result
     *
     * @return array
     */
    public function getImageIdentifiers(array $product, array &$result);

    /**
     * @param array $variation
     *
     * @return string
     */
    public function getUnitIdentifier(array $variation);

    /**
     * @param array $variation
     *
     * @return string
     */
    public function getVatRateIdentifier(array $variation);

    /**
     * @param array $product
     *
     * @return string
     */
    public function getManufacturerIdentifier(array $product);

    /**
     * @param array $product
     *
     * @return array
     */
    public function getShippingProfiles(array $product);

    /**
     * @param array $mainVariation
     * @param array $webstores
     *
     * @return array
     */
    public function getDafaultCategories(array $mainVariation, array $webstores);

    /**
     * @param array $texts
     *
     * @return array
     */
    public function getProductTranslations(array $texts);

    /**
     * @param $product
     * @param $variation
     *
     * @return int
     */
    public function getStock($product, $variation);

    /**
     * @param array $mainVariation
     * @param array $webstores
     *
     * @return array
     */
    public function getCategories(array $mainVariation, array $webstores);

    /**
     * @param array $product
     *
     * @return Attribute[]
     */
    public function getAttributes(array $product);
}
