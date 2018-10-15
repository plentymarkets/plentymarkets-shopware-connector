<?php

namespace PlentymarketsAdapter\ReadApi\Item;

use PlentymarketsAdapter\ReadApi\ApiAbstract;

class Variation extends ApiAbstract
{
    /**
     * @var array
     */
    private $includes = [
        'variationClients',
        'variationSalesPrices',
        'variationCategories',
        'variationDefaultCategory',
        'unit',
        'variationAttributeValues',
        'variationBarcodes',
        'images',
        'stock',
        'variationProperties',
    ];

    /**
     * @param array $criteria
     *
     * @return array
     */
    public function findBy(array $criteria)
    {
        $params = array_merge($criteria, [
            'with' => implode(',', $this->includes),
        ]);

        return iterator_to_array($this->client->getIterator('items/variations', $params));
    }
}
