<?php

namespace PlentyConnector\Connector\ValueObject\Attribute;

use PlentyConnector\Connector\TransferObject\TranslateableInterface;
use PlentyConnector\Connector\ValueObject\ValueObjectInterface;

/**
 * Interface AttributeInterface
 */
interface AttributeInterface extends ValueObjectInterface, TranslateableInterface
{
    /**
     * @return string
     */
    public function getKey();

    /**
     * @return string
     */
    public function getValue();
}
