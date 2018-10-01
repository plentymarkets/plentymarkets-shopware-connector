<?php

namespace ShopwareAdapter\DataProvider\Currency;

interface CurrencyDataProviderInterface
{
    /**
     * @param string $code
     *
     * @return int
     */
    public function getCurrencyIdentifierByCode($code);
}
