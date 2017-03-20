<?php


namespace PlentymarketsAdapter\ReadApi\Item\Attribute;


use PlentymarketsAdapter\ReadApi\ApiAbstract;

/**
 * Class ValueName
 */
class ValueName extends ApiAbstract
{
    /**
     * @param $attributeId
     * @return mixed
     */
    public function findOne($attributeId)
    {
        return $this->client->request('GET', 'items/attribute_values/' . $attributeId . '/names');
    }
}