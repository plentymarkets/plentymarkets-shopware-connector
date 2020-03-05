<?php

namespace PlentymarketsAdapter\ReadApi\Item;

use PlentymarketsAdapter\ReadApi\ApiAbstract;

class Unit extends ApiAbstract
{
    public function findAll(): array
    {
        return iterator_to_array($this->client->getIterator('items/units'));
    }
}
