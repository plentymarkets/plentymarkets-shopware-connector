<?php

namespace PlentyConnector\Connector\TransferObject\Product\Property\Value;

use PlentyConnector\Connector\TransferObject\TranslateableInterface;
use PlentyConnector\Connector\ValueObject\ValueObjectInterface;

/**
 * Interface ValueInterface
 */
interface ValueInterface extends ValueObjectInterface, TranslateableInterface
{
    /**
     * @return string
     */
    public function getValue();
}
