<?php

namespace PlentymarketsAdapter\ReadApi\Item\Property;

use PlentymarketsAdapter\ReadApi\ApiAbstract;

/**
 * Class Group
 */
class Group extends ApiAbstract
{
    /**
     * @param int $propertyId
     *
     * @return array
     */
    public function findOne($propertyGroupId)
    {
        return $this->client->request('GET', 'items/property_groups/' . $propertyGroupId . '/names');
    }
}
