<?php

namespace PlentymarketsAdapter\ReadApi\Item;

use PlentymarketsAdapter\ReadApi\ApiAbstract;

/**
 * Class Attribute
 */
class Attribute extends ApiAbstract
{
    /**
     * @param $attributeId
     *
     * @return array
     */
    public function findOne($attributeId)
    {
        return $this->client->request('GET', 'items/attributes/' . $attributeId, [
            'with' => 'names',
        ]);
    }

    /**
     * @return array
     */
    public function findAll()
    {
        return $this->client->request('GET', 'items/attributes/', [
            'with' => 'names',
        ]);
    }
}
