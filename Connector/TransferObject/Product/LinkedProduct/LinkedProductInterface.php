<?php

namespace PlentyConnector\Connector\TransferObject\Product\LinkedProduct;

use PlentyConnector\Connector\ValueObject\ValueObjectInterface;

/**
 * Interface LinkedProductInterface
 */
interface LinkedProductInterface extends ValueObjectInterface
{
    const TYPE_ACCESSORY = 'Accessory';
    const TYPE_REPLACEMENT = 'Replacement';
    const TYPE_SIMILAR = 'Similar';

    /**
     * @return string
     */
    public function getType();

    /**
     * @return string
     */
    public function getProductIdentifier();
}
