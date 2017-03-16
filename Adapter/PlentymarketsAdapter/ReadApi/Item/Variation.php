<?php


namespace PlentymarketsAdapter\ReadApi\Item;


use PlentymarketsAdapter\ReadApi\ApiAbstract;

/**
 * Class ItemsVariations
 */
class Variation extends ApiAbstract
{

    /**
     * @param null $productId
     * @return array
     */
    public function findOne($productId)
    {
        return $this->client->request('GET', 'items/' . $productId . '/variations', [
            'with' => 'variationClients,variationSalesPrices,variationCategories,variationDefaultCategory,unit,variationAttributeValues,variationBarcodes',
        ]);
    }
}