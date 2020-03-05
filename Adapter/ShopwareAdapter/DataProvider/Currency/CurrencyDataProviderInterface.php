<?php

namespace ShopwareAdapter\DataProvider\Currency;

interface CurrencyDataProviderInterface
{
    /**
     * @param string $code
     */
    public function getCurrencyIdentifierByCode($code): int;
}
