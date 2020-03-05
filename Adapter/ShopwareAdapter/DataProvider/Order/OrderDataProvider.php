<?php

namespace ShopwareAdapter\DataProvider\Order;

use Doctrine\DBAL\Connection;
use Shopware\Components\Api\Resource\Order as OrderResource;
use Shopware\Models\Order\Status;

class OrderDataProvider implements OrderDataProviderInterface
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var OrderResource
     */
    private $orderResource;

    public function __construct(Connection $connection, OrderResource $orderResource)
    {
        $this->connection = $connection;
        $this->orderResource = $orderResource;
    }

    public function getOpenOrders(): array
    {
        $filter = [
            [
                'property' => 'status',
                'expression' => '=',
                'value' => Status::ORDER_STATE_OPEN,
            ],
        ];

        $orders = $this->orderResource->getList(0, null, $filter);

        return $orders['data'];
    }

    /**
     * {@inheritdoc}
     */
    public function getOrderDetails($identifier): array
    {
        $order = $this->orderResource->getOne($identifier);

        $order['shopId'] = $this->getCorrectSubShopIdentifier($identifier);

        return $this->removeOrphanedShopArray($order);
    }

    /**
     * @param int $orderIdentifier
     */
    private function getCorrectSubShopIdentifier($orderIdentifier): int
    {
        return $this->connection->fetchColumn('SELECT language FROM s_order WHERE id = ?', [$orderIdentifier]);
    }

    private function removeOrphanedShopArray(array $order): array
    {
        unset($order['shop']);

        return $order;
    }
}
