<?php

namespace PlentymarketsAdapter\ResponseParser\Product;

use phpDocumentor\Reflection\DocBlock\Tags\Property;
use PlentyConnector\Connector\TransferObject\Product\LinkedProduct\LinkedProduct;
use PlentyConnector\Connector\TransferObject\Product\Variation\Variation;
use PlentyConnector\Connector\ValueObject\Translation\Translation;

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
     * @param array $product
     * @param array $texts
     * @param $result
     *
     * @return array
     */
    public function getImageIdentifiers(array $product, array $texts, array &$result);

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
     * @return Translation[]
     */
    public function getProductTranslations(array $texts);

    /**
     * @param $variation
     *
     * @return int
     */
    public function getStock($variation);

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

    /**
     * @param array $texts
     * @param array $variations
     * @param array $result
     *
     * @return Variation[]
     */
    public function getVariations(array $texts, $variations, array &$result);

    /**
     * @param $product
     *
     * @return LinkedProduct[]
     */
    public function getLinkedProducts(array $product);

    /**
     * @param array $product
     *
     * @return array
     */
    public function getDocuments(array $product);

    /**
     * @param $product
     *
     * @return Property[]
     */
    public function getProperties(array $product);

    /**
     * @param array $mainVariation
     *
     * @return array
     */
    public function getShopIdentifiers(array $mainVariation);
}
