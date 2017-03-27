<?php

namespace PlentymarketsAdapter\ReadApi\Item;

use PlentymarketsAdapter\ReadApi\ApiAbstract;

/**
 * Class ItemSalesPrices.
 */
class SalesPrice extends ApiAbstract
{
    /**
     * @return array
     */
    public function findAll()
    {
        return $this->client->request('GET', 'items/sales_prices');
    }

    /**
     * @param $priceId
     *
     * @return array
     */
    public function findOne($priceId)
    {
        return $this->client->request('GET', 'items/sales_prices/'.$priceId);
    }
}
