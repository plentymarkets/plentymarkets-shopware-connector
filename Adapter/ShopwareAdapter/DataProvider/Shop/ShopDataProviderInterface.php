<?php

namespace ShopwareAdapter\DataProvider\Shop;

use Shopware\Models\Shop\Shop as ShopModel;

interface ShopDataProviderInterface
{
    /**
     * @return ShopModel
     */
    public function getDefaultShop(): ShopModel;
}
