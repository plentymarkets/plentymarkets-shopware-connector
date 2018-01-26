<?php

namespace PlentymarketsAdapter\ReadApi\Item;

use PlentymarketsAdapter\ReadApi\ApiAbstract;

/**
 * Class SalesPrice
 */
class SalesPrice extends ApiAbstract
{
    /**
     * @return array
     */
    public function findAll()
    {
        return iterator_to_array($this->client->getIterator('items/sales_prices'));
    }

    /**
     * @param int $priceId
     *
     * @return array
     */
    public function findOne($priceId)
    {
        return $this->client->request('GET', 'items/sales_prices/' . $priceId);
    }
}
