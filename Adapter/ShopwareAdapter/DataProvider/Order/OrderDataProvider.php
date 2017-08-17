<?php

namespace ShopwareAdapter\DataProvider\Order;

use Doctrine\DBAL\Connection;
use Shopware\Components\Api\Resource\Order as OrderResource;
use Shopware\Models\Order\Status;

/**
 * Class OrderDataProvider
 */
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

    /**
     * OrderDataProvider constructor.
     *
     * @param Connection    $connection
     * @param OrderResource $orderResource
     */
    public function __construct(Connection $connection, OrderResource $orderResource)
    {
        $this->connection = $connection;
        $this->orderResource = $orderResource;
    }

    /**
     * {@inheritdoc}
     */
    public function getOpenOrders()
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
    public function getOrderDetails($identifier)
    {
        $order = $this->orderResource->getOne($identifier);

        $order['shopId'] = $this->getCorrectSubShopIdentifier($identifier);

        return $this->removeOrphanedShopArray($order);
    }

    /**
     * @param int $orderIdentifier
     *
     * @return int
     */
    private function getCorrectSubShopIdentifier($orderIdentifier)
    {
        return $this->connection->fetchColumn('SELECT language FROM s_order WHERE id = ?', [$orderIdentifier]);
    }

    /**
     * @param array $order
     *
     * @return array
     */
    private function removeOrphanedShopArray(array $order)
    {
        unset($order['shop']);

        return $order;
    }
}
