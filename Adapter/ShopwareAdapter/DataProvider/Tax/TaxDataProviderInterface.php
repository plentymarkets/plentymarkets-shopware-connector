<?php

namespace ShopwareAdapter\DataProvider\Tax;

use Shopware\Models\Tax\Tax;

interface TaxDataProviderInterface
{
    /**
     * @param string $rate
     * @param int    $countryId
     *
     * @return Tax $taxModel|null
     */
    public function getTax(string $rate, int $countryId);
}
