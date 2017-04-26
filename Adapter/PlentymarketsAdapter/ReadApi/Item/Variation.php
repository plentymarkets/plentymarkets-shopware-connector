<?php

namespace PlentymarketsAdapter\ReadApi\Item;

use PlentymarketsAdapter\ReadApi\ApiAbstract;

/**
 * Class Variation
 */
class Variation extends ApiAbstract
{
    /**
     * @param int $productId
     *
     * @return array
     */
    public function findOne($productId)
    {
        return $this->client->request('GET', 'items/' . $productId . '/variations', [
            'with' => 'variationClients,variationSalesPrices,variationCategories,variationDefaultCategory,unit,variationAttributeValues,variationBarcodes,images,stock',
        ]);
    }
}
