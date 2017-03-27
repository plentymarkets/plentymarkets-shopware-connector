<?php

namespace PlentymarketsAdapter\ReadApi\Item\Variation;

use PlentymarketsAdapter\ReadApi\ApiAbstract;

/**
 * Class Image.
 */
class Image extends ApiAbstract
{
    /**
     * @param $productId
     * @param $variationId
     *
     * @return mixed
     */
    public function findOne($productId, $variationId)
    {
        $url = 'items/'.$productId.'/variations/'.$variationId.'/images';

        return $this->client->request('GET', $url);
    }
}
