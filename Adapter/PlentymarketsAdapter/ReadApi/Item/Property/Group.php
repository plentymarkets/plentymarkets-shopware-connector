<?php

namespace PlentymarketsAdapter\ReadApi\Item\Property;

use PlentymarketsAdapter\ReadApi\ApiAbstract;

class Group extends ApiAbstract
{
    /**
     * @var array
     */
    private $includes = [
        'names',
    ];

    /**
     * @param int $propertyGroupId
     */
    public function findOne($propertyGroupId): array
    {
        return $this->client->request('GET', 'items/property_groups/' . $propertyGroupId . '/names');
    }

    public function findAll()
    {
        return iterator_to_array($this->client->getIterator('items/property_groups', [
            'with' => implode(',', $this->includes),
        ], function (array $elements) {
            return $elements;
        }));
    }
}
