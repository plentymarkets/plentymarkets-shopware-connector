<?php

namespace PlentymarketsAdapter\ReadApi\Item\Property;

use PlentymarketsAdapter\ReadApi\ApiAbstract;

class Name extends ApiAbstract
{
    /**
     * @param int $propertyId
     *
     * @return array
     */
    public function findOne($propertyId): array
    {
        return $this->client->request('GET', 'items/properties/' . $propertyId . '/names');
    }
}
