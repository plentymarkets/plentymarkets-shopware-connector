<?php

namespace PlentymarketsAdapter\ReadApi\Item\Variation;

use PlentymarketsAdapter\ReadApi\ApiAbstract;

/**
 * Class Property
 */
class Property extends ApiAbstract
{
    /**
     * @param int $productId
     * @param int $variationId
     *
     * @return mixed
     */
    public function findOne($productId, $variationId)
    {
        $url = 'items/' . $productId . '/variations/' . $variationId . '/variation_properties';

        return $this->client->request('GET', $url);
    }
}
