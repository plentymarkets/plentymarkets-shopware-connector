<?php

namespace PlentymarketsAdapter\ReadApi\Item\Variation;

use PlentymarketsAdapter\ReadApi\ApiAbstract;

/**
 * Class Property
 */
class Property extends ApiAbstract
{
    /**
     * @param $productId
     * @param $variationId
     * @return mixed
     */
    public function findOne($productId, $variationId)
    {
        return $this->client->request(
            'GET',
            'items/' . $productId . '/variations/' . $variationId . '/variation_properties'
        );
    }

}