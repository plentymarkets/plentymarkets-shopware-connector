<?php

namespace ShopwareAdapter\DataProvider\CustomerGroup;

use Shopware\Models\Customer\Group;

interface CustomerGroupDataProviderInterface
{
    /**
     * @param int $identifier
     *
     * @return null|Group
     */
    public function getCustomerGroupByShopwareIdentifier($identifier);

    /**
     * @param int $identifier
     *
     * @return null|string
     */
    public function getCustomerGroupKeyByShopwareIdentifier($identifier);
}
