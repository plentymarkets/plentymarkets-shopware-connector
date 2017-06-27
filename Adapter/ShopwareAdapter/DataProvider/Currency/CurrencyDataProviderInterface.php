<?php

namespace ShopwareAdapter\DataProvider\Currency;

/**
 * Interface CurrencyDataProviderInterface
 */
interface CurrencyDataProviderInterface
{
    /**
     * @param $code
     *
     * @return int
     */
    public function getCurrencyIdentifierByCode($code);
}
