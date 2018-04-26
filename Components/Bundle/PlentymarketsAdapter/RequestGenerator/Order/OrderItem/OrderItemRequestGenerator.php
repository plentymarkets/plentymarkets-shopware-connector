<?php

namespace PlentyConnector\Components\Bundle\PlentymarketsAdapter\RequestGenerator\Order\OrderItem;

use Doctrine\DBAL\Connection;
use Exception;
use PlentymarketsAdapter\RequestGenerator\Order\OrderItem\OrderItemRequestGeneratorInterface;
use PlentyConnector\Connector\TransferObject\Order\Order;
use PlentyConnector\Connector\TransferObject\Order\OrderItem\OrderItem;

/**
 * Class OrderItemRequestGenerator
 */
class OrderItemRequestGenerator implements OrderItemRequestGeneratorInterface
{
    /**
     * @var OrderItemRequestGeneratorInterface
     */
    private $parentOrderItemRequestGenerator;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * OrderItemRequestGenerator constructor.
     * @param OrderItemRequestGeneratorInterface $parentOrderItemRequestGenerator
     * @param Connection $connection
     */
    public function __construct(
        OrderItemRequestGeneratorInterface $parentOrderItemRequestGenerator,
        Connection $connection
    ) {
        $this->parentOrderItemRequestGenerator = $parentOrderItemRequestGenerator;
        $this->connection = $connection;
    }

    /**
     * @param OrderItem $orderItem
     * @param Order $order
     * @return array
     */
    public function generate(OrderItem $orderItem, Order $order)
    {
        $itemParams = $this->parentOrderItemRequestGenerator->generate($orderItem, $order);

        if (!$this->getBundle($orderItem->getNumber())) {
            return $itemParams;
        }

       //$itemParams['orderItemName'] = '[Bundle] ' . $itemParams['orderItemName'];

        return $itemParams;
    }

    /**
     * @param int $articleNumber
     *
     * @return array|false
     */
    private function getBundle($articleNumber)
    {
        try {
            $query = 'SELECT * FROM s_articles_bundles AS bundle 
                      LEFT JOIN s_articles_bundles_prices AS bundlePrice
                      ON bundle.id = bundlePrice.bundle_id
                      WHERE ordernumber = ?
                      AND bundlePrice.customer_group_id = 1';

            return $this->connection->fetchAll($query, [$articleNumber]);
        } catch (Exception $exception) {
            return false;
        }
    }
}
