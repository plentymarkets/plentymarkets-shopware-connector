<?php


namespace PlentymarketsAdapter\ReadApi\Item\Property;

use PlentymarketsAdapter\ReadApi\ApiAbstract;


/**
 * Class Name
 */
class Name extends ApiAbstract
{
    public function findOne($propertyId)
    {
        return $this->client->request('GET',
            'items/properties/' . $propertyId . '/names');
    }

}