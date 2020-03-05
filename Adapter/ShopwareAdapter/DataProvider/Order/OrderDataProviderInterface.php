<?php

namespace ShopwareAdapter\DataProvider\Order;

interface OrderDataProviderInterface
{
    public function getOpenOrders(): array;

    /**
     * @param int $identifier
     */
    public function getOrderDetails($identifier): array;
}
