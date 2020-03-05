<?php

namespace PlentymarketsAdapter\ReadApi\Item;

use PlentymarketsAdapter\ReadApi\ApiAbstract;

class SalesPrice extends ApiAbstract
{
    public function findAll(): array
    {
        return iterator_to_array($this->client->getIterator('items/sales_prices'));
    }

    /**
     * @param int $priceId
     */
    public function findOne($priceId): array
    {
        return $this->client->request('GET', 'items/sales_prices/' . $priceId);
    }
}
