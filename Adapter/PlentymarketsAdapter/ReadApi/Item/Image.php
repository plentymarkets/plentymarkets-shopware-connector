<?php

namespace PlentymarketsAdapter\ReadApi\Item;

use PlentymarketsAdapter\ReadApi\ApiAbstract;

/**
 * Class Image
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
        return $this->client->request('GET', 'items/' . $productId . '/images');
    }
}
