<?php

namespace PlentymarketsAdapter\ReadApi\Item;

use PlentymarketsAdapter\ReadApi\ApiAbstract;

class Barcode extends ApiAbstract
{
    public function findAll(): array
    {
        return iterator_to_array($this->client->getIterator('items/barcodes'));
    }
}
