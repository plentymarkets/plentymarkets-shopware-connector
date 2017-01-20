<?php

namespace PlentyConnector\Connector\TransferObject\Currency;

use PlentyConnector\Connector\TransferObject\TransferObjectInterface;

/**
 * Interface CurrencyInterface
 */
interface CurrencyInterface extends TransferObjectInterface
{
    /**
     * @return string
     */
    public function getName();
}
