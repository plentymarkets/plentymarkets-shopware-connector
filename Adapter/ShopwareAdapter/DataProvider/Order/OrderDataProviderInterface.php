<?php

namespace ShopwareAdapter\DataProvider\Order;

/**
 * Interface OrderDataProviderInterface
 */
interface OrderDataProviderInterface
{
    /**
     * @return array
     */
    public function getOpenOrders();

    /**
     * @param int $identifier
     *
     * @return array
     */
    public function getOrderDetails($identifier);
}
