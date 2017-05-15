<?php

namespace PlentymarketsAdapter\ReadApi\Item\Attribute;

use PlentymarketsAdapter\ReadApi\ApiAbstract;

/**
 * Class Name
 */
class Name extends ApiAbstract
{
    /**
     * @param $attributeId
     *
     * @return array
     */
    public function findOne($attributeId)
    {
        return $this->client->request('GET', 'items/attributes/' . $attributeId . '/names');
    }
}
