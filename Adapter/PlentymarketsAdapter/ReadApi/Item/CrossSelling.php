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
     * @return mixed
     */
    public function findAll($productId)
    {
        return $this->client->request('GET', 'items/' . $productId . '/item_cross_selling');
    }
}
