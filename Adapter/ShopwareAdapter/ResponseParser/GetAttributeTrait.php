<?php

namespace ShopwareAdapter\ResponseParser;

use PlentyConnector\Connector\ValueObject\Attribute\Attribute;

/**
 * Class GetAttributeTrait
 */
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

            if ($value instanceof \DateTime) {
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
