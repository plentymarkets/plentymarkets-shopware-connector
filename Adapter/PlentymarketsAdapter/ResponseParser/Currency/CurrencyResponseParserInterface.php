<?php

namespace PlentymarketsAdapter\ResponseParser\Currency;

use PlentyConnector\Connector\TransferObject\Currency\CurrencyInterface;

/**
 * Interface CurrencyResponseParserInterface
 */
interface CurrencyResponseParserInterface
{
    /**
     * @param array $entry
     *
     * @return CurrencyInterface|null
     */
    public function parse(array $entry);
}
