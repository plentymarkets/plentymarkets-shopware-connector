<?php

namespace PlentymarketsAdapter\ReadApi\Item\Attribute;

use PlentymarketsAdapter\ReadApi\ApiAbstract;

/**
 * Class Value
 */
class Value extends ApiAbstract
{
    /**
     * @param int $attributeId
     *
     * @return array
     */
    public function findOne($attributeId)
    {
        return iterator_to_array($this->client->getIterator('items/attributes/' . $attributeId . '/values', [
            'with' => 'names,attribute',
        ]));
    }
}
