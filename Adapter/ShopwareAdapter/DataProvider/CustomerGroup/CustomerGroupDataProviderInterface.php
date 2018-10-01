<?php

namespace ShopwareAdapter\DataProvider\CustomerGroup;

interface CustomerGroupDataProviderInterface
{
    /**
     * @param int $identifier
     *
     * @return null|string
     */
    public function getCustomerGroupKeyByShopwareIdentifier($identifier);
}
