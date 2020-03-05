<?php

namespace ShopwareAdapter\DataProvider\Tax;

use Shopware\Models\Tax\Tax;

interface TaxDataProviderInterface
{
    /**
     * @return Tax $taxModel|null
     */
    public function getTax(float $rate, int $countryId);
}
