<?php

namespace PlentymarketsAdapter\ResponseParser;

use PlentyConnector\Connector\ValueObject\Attribute\Attribute;

/**
 * Class GetAttributeTrait.
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

            $attribute = new Attribute();
            $attribute->setKey((string) $key);
            $attribute->setValue((string) $value);

            $attributes[] = $attribute;
        }

        return $attributes;
    }
}
