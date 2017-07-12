<?php

namespace ShopwareAdapter\DataProvider\CustomerGroup;

/**
 * Interface CustomerGroupDataProviderInterface
 */
interface CustomerGroupDataProviderInterface
{
    /**
     * @param int $identifier
     *
     * @return null|string
     */
    public function getCustomerGroupKeyByShopwareIdentifier($identifier);
}
