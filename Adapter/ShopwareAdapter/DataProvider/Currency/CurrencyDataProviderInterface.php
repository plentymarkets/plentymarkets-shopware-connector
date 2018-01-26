<?php

namespace ShopwareAdapter\DataProvider\Currency;

/**
 * Interface CurrencyDataProviderInterface
 */
interface CurrencyDataProviderInterface
{
    /**
     * @param string $code
     *
     * @return int
     */
    public function getCurrencyIdentifierByCode($code);
}
