<?php

namespace ShopwareAdapter\DataProvider\Shop;

use Shopware\Models\Shop\Shop as ShopModel;

/**
 * Interface ShopDataProviderInterface
 */
interface ShopDataProviderInterface
{
    /**
     * @return ShopModel
     */
    public function getDefaultShopLocaleIdentitiy();


}
