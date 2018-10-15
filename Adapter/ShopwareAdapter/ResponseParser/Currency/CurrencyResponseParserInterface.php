<?php

namespace ShopwareAdapter\ResponseParser\Currency;

use SystemConnector\TransferObject\Currency\Currency;

interface CurrencyResponseParserInterface
{
    /**
     * @param array $entry
     *
     * @return null|Currency
     */
    public function parse(array $entry);
}
