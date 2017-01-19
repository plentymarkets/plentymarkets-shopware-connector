<?php

namespace PlentyConnector\Connector\TransferObject;

use PlentyConnector\Connector\ValueObject\Attribute\Attribute;

/**
 * Interface AttributeableInterface
 */
interface AttributeableInterface
{
    /**
     * @return Attribute[]
     */
    public function getAttributes();
}
