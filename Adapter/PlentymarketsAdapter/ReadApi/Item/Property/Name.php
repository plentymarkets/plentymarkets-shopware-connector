<?php

namespace PlentymarketsAdapter\ReadApi\Item\Property;

use PlentymarketsAdapter\ReadApi\ApiAbstract;

class Name extends ApiAbstract
{
    /**
     * @var array
     */
    private $includes = [
        'names',
    ];

    /**
     * @param int $propertyId
     *
     * @return array
     */
    public function findOne($propertyId)
    {
        return $this->client->request('GET', 'items/properties/' . $propertyId . '/names');
    }

    /**
     * @return array
     */
    public function findAll()
    {
        return iterator_to_array($this->client->getIterator('items/properties', [
            'with' => implode(',', $this->includes),
        ], function (array $elements) {
            return $elements;
        }));
    }
}
