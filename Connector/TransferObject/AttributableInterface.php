<?php

namespace PlentyConnector\Connector\TransferObject;

use PlentyConnector\Connector\ValueObject\Attribute\Attribute;

interface AttributableInterface
{
    /**
     * @return Attribute[]
     */
    public function getAttributes();

    /**
     * @param Attribute[] $attributes
     */
    public function setAttributes(array $attributes);
}
