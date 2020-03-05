<?php

namespace PlentymarketsAdapter\ReadApi\Item;

use PlentymarketsAdapter\ReadApi\ApiAbstract;

class Attribute extends ApiAbstract
{
    /**
     * @var array
     */
    private $includes = [
        'names',
        'values.valueNames',
    ];

    /**
     * @param int $attributeId
     */
    public function findOne($attributeId): array
    {
        return $this->client->request('GET', 'items/attributes/' . $attributeId, [
            'with' => implode(',', $this->includes),
        ]);
    }

    public function findAll(): array
    {
        return iterator_to_array($this->client->getIterator('items/attributes/', [
            'with' => implode(',', $this->includes),
        ]));
    }
}
