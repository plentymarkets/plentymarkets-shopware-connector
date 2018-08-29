<?php

namespace PlentymarketsAdapter\ReadApi\Item;

use PlentymarketsAdapter\ReadApi\ApiAbstract;

/**
 * Class Attribute
 */
class Attribute extends ApiAbstract
{
    private $includes = 'names,values.valueNames';

    /**
     * @param int $attributeId
     *
     * @return array
     */
    public function findOne($attributeId)
    {
        return $this->client->request('GET', 'items/attributes/' . $attributeId, [
            'with' => $this->includes,
        ]);
    }

    /**
     * @return array
     */
    public function findAll()
    {
        return iterator_to_array($this->client->getIterator('items/attributes/', [
            'with' => $this->includes,
        ]));
    }
}
