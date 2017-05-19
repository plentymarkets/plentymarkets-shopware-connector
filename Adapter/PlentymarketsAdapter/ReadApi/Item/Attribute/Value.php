<?php

namespace PlentymarketsAdapter\ReadApi\Item\Attribute;

use PlentymarketsAdapter\Client\Iterator\Iterator;
use PlentymarketsAdapter\ReadApi\ApiAbstract;

/**
 * Class Value
 */
class Value extends ApiAbstract
{
    /**
     * @param $attributeId
     *
     * @return array
     */
    public function findOne($attributeId)
    {
        return iterator_to_array($this->client->getIterator('items/attributes/' . $attributeId . '/values', [
            'with' => 'names',
        ]));
    }
}
