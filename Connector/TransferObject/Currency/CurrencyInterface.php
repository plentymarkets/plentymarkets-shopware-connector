<?php

namespace PlentyConnector\Connector\TransferObject\Currency;

use PlentyConnector\Connector\TransferObject\MappedTransferObjectInterface;

/**
 * Interface CurrencyInterface
 */
interface CurrencyInterface extends MappedTransferObjectInterface
{
    /**
     * ISO 4217 based currency name
     *
     * @return string
     */
    public function getCurrency();
}
