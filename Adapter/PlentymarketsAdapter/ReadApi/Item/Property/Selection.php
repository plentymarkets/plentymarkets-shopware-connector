<?php

namespace PlentymarketsAdapter\ReadApi\Item\Property;

use PlentymarketsAdapter\ReadApi\ApiAbstract;

/**
 * Class Selection
 */
class Selection extends ApiAbstract
{
    /**
     * @param $propertyId
     *
     * @return array
     */
    public function findOne($propertyId)
    {
        return $this->client->request('GET', 'items/properties/' . $propertyId . '/selections');
    }
}
