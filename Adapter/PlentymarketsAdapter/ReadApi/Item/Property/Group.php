<?php

namespace PlentymarketsAdapter\ReadApi\Item\Property;

use PlentymarketsAdapter\ReadApi\ApiAbstract;

class Group extends ApiAbstract
{
    /**
     * @param int $propertyGroupId
     *
     * @return array
     */
    public function findOne($propertyGroupId)
    {
        return $this->client->request('GET', 'items/property_groups/' . $propertyGroupId . '/names');
    }
}
