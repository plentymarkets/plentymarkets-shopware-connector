<?php

namespace PlentymarketsAdapter\ReadApi\Item\Attribute;

use PlentymarketsAdapter\ReadApi\ApiAbstract;

/**
 * Class Value
 */
class Value extends ApiAbstract
{
    /**
     * @param $attributeId
     *
     * @return mixed
     */
    public function findOne($attributeId)
    {
        return $this->client->request('GET', 'items/attributes/' . $attributeId . '/values');
    }
}
