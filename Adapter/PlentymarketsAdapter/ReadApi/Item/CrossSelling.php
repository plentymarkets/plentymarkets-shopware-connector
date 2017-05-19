<?php

namespace PlentymarketsAdapter\ReadApi\Item;

use PlentymarketsAdapter\ReadApi\ApiAbstract;

/**
 * Class CrossSelling
 */
class CrossSelling extends ApiAbstract
{
    /**
     * @param $productId
     *
     * @return array
     */
    public function findAll($productId)
    {
        return iterator_to_array($this->client->getIterator('items/' . $productId . '/item_cross_selling'));
    }
}
