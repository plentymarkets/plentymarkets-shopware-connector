<?php

namespace PlentymarketsAdapter\ReadApi\Item\Variation;

use PlentymarketsAdapter\ReadApi\ApiAbstract;

/**
 * Class Stock
 */
class Stock extends ApiAbstract
{
    /**
     * @param $productId
     * @param $variationId
     *
     * @return mixed
     */
    public function findOne($productId, $variationId)
    {
        $url = 'items/' . $productId . '/variations/' . $variationId . '/stock';

        return $this->client->request('GET', $url);
    }
}
