<?php

namespace PlentyConnector\Connector\TransferObject\Product\Price;

use PlentyConnector\Connector\ValueObject\ValueObjectInterface;

/**
 * Interface PriceInterface
 */
interface PriceInterface extends ValueObjectInterface
{
    /**
     * @return float
     */
    public function getPrice();

    /**
     * @return float
     */
    public function getPseudoPrice();

    /**
     * @return string|null
     */
    public function getCustomerGroupIdentifier();

    /**
     * @return int
     */
    public function getFromAmount();

    /**
     * @return int
     */
    public function getToAmount();
}
