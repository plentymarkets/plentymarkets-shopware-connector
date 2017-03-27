<?php

namespace PlentymarketsAdapter\ReadApi\Item;

use PlentymarketsAdapter\ReadApi\ApiAbstract;

/**
 * Class Images.
 */
class Image extends ApiAbstract
{
    /**
     * @param null $productId
     *
     * @return array
     */
    public function findAll($productId)
    {
        $url = 'items/'.$productId.'/images';

        return $images = $this->client->request('GET', $url);
    }
}
