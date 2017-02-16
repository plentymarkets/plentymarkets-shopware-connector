<?php

namespace PlentyConnector\Connector\TransferObject\Product\Property;

use PlentyConnector\Connector\TransferObject\Product\Property\Value\ValueInterface;
use PlentyConnector\Connector\TransferObject\TranslateableInterface;
use PlentyConnector\Connector\ValueObject\ValueObjectInterface;

/**
 * Interface PropertyInterface
 */
interface PropertyInterface extends ValueObjectInterface, TranslateableInterface
{
    /**
     * @return string
     */
    public function getKey();

    /**
     * @return ValueInterface[]
     */
    public function getValues();
}
