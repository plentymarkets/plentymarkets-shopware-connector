<?php

namespace PlentymarketsAdapter\ReadApi\Item;

use PlentymarketsAdapter\ReadApi\ApiAbstract;

class Barcode extends ApiAbstract
{
    /**
     * @return array
     */
    public function findAll()
    {
        return iterator_to_array($this->client->getIterator('items/barcodes'));
    }
}
