<?php

namespace SystemConnector\TransferObject;

use SystemConnector\ValueObject\Attribute\Attribute;

interface AttributableInterface
{
    /**
     * @return Attribute[]
     */
    public function getAttributes() :array;

    /**
     * @param Attribute[] $attributes
     */
    public function setAttributes(array $attributes);
}
