<?php

namespace ShopwareAdapter\DataProvider\Shop;

use Shopware\Models\Shop\Shop as ShopModel;

interface ShopDataProviderInterface
{
    public function getDefaultShop(): ShopModel;
}
