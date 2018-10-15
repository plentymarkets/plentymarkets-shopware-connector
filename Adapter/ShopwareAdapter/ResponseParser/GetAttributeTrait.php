<?php

namespace ShopwareAdapter\ResponseParser;

use DateTimeInterface;
use SystemConnector\ValueObject\Attribute\Attribute;

trait GetAttributeTrait
{
    /**
     * @param array $attributeData
     *
     * @return Attribute[]
     */
    private function getAttributes(array $attributeData)
    {
        $attributes = [];

        foreach ($attributeData as $key => $value) {
            if (empty($value)) {
                continue;
            }

            if ($value instanceof DateTimeInterface) {
                $strValue = $value->format('Y-m-d H:i:s');
            } else {
                $strValue = (string) $value;
            }

            $attribute = new Attribute();
            $attribute->setKey((string) $key);
            $attribute->setValue($strValue);

            $attributes[] = $attribute;
        }

        return $attributes;
    }
}
