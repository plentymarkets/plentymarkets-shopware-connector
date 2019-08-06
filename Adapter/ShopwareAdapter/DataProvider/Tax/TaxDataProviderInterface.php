<?php

namespace ShopwareAdapter\DataProvider\Tax;

use Shopware\Models\Tax\Tax;

interface TaxDataProviderInterface
{
    /**
     * @param float $rate
     * @param int   $countryId
     *
     * @return Tax $taxModel|null
     */
    public function getTax(float $rate, int $countryId);
}
