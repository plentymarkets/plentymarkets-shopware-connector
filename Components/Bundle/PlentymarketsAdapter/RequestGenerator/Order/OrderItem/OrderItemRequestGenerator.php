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
    )
    {
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
        $itemParams = $this->parentOrderItemRequestGenerator->generate($orderItem , $order);

        foreach ($orderItem->getAttributes() as $attribute) {

            if (!$attribute->getKey() === 'bundlePackageId' && !$attribute->getValue()) {
                continue;
            }

            if (!$this->isBundle($orderItem)) {
                $itemParams['typeId'] = 3;
                $itemParams['amounts'][0]['priceOriginalGross'] = 0;
                $itemParams['referrerId'] = 423555545;

            }else{
                $itemParams['typeId'] = 2;
                $itemParams['orderItemName'] = '[BUNDLE] ' . $itemParams['orderItemName'];
            }
        }

        return $itemParams;
    }

    /**
     * @param OrderItem $orderItem
     *
     * @return bool
     */
    private function isBundle(OrderItem $orderItem)
    {
        try {
            $query = 'SELECT * FROM s_articles_bundles WHERE ordernumber = ?';

            return $this->connection->fetchColumn($query, [$orderItem->getNumber()]);
        } catch (Exception $exception) {
            return false;
        }

        return $orderItem->getType() === OrderItem::TYPE_VOUCHER || $orderItem->getType() === OrderItem::TYPE_COUPON;
    }
}
