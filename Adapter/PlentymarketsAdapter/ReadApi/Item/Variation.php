<?php

namespace PlentymarketsAdapter\ReadApi\Item;

use PlentymarketsAdapter\ReadApi\ApiAbstract;

/**
 * Class Variation
 */
class Variation extends ApiAbstract
{
    private $includes = 'variationClients,variationSalesPrices,variationCategories,variationDefaultCategory,unit,variationAttributeValues,variationBarcodes,images,stock,variationProperties';

    /**
     * @param array $criteria
     *
     * @return array
     */
    public function findBy(array $criteria)
    {
        $params = array_merge($criteria, [
            'with' => $this->includes,
        ]);

        return iterator_to_array($this->client->getIterator('items/variations', $params));
    }
}
