<?php

namespace ShopwareAdapter\ResponseParser\Currency;

use PlentyConnector\Connector\TransferObject\Currency\Currency;

/**
 * Interface CurrencyResponseParserInterface
 */
interface CurrencyResponseParserInterface
{
    /**
     * @param array $entry
     *
     * @return null|Currency
     */
    public function parse(array $entry);
}
