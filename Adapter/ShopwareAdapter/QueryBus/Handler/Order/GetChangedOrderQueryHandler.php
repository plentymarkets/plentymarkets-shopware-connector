<?php

namespace ShopwareAdapter\QueryBus\Handler\Order;

use Exception;
use PlentyConnector\Connector\Config\ConfigInterface;
use PlentyConnector\Connector\QueryBus\Handler\QueryHandlerInterface;
use PlentyConnector\Connector\QueryBus\Query\Manufacturer\GetManufacturerQuery;
use PlentyConnector\Connector\QueryBus\Query\Order\GetChangedOrderQuery;
use PlentyConnector\Connector\TransferObject\Order\OrderInterface;
use ShopwareAdapter\ResponseParser\ResponseParserInterface;
use Psr\Log\LoggerInterface;
use Shopware\Components\Api\Manager;
use Shopware\Components\Api\Resource;
use ShopwareAdapter\ShopwareAdapter;

/**
 * Class GetChangedOrderQueryHandler
 */
class GetChangedOrderQueryHandler implements QueryHandlerInterface
{
    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @var ResponseParserInterface
     */
    private $responseParser;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * GetChangedOrderQueryHandler constructor.
     * @param ConfigInterface $config
     * @param ResponseParserInterface $responseParser
     * @param LoggerInterface $logger
     */
    public function __construct(
        ConfigInterface $config,
        ResponseParserInterface $responseParser,
        LoggerInterface $logger
    ) {
        $this->config = $config;
        $this->responseParser = $responseParser;
        $this->logger = $logger;
    }

    /**
     * @param GetChangedOrderQuery $event
     *
     * @return bool
     */
    public function supports($event)
    {
        return
            $event instanceof GetChangedOrderQuery &&
            $event->getAdapterName() === ShopwareAdapter::getName()
            ;
    }

    /**
     * @param GetManufacturerQuery $event
     *
     * @return OrderInterface[]
     *
     * @throws \UnexpectedValueException
     */
    public function handle($event)
    {
        /**
         * @var Resource\Order $orderResource
         */
        $orderResource = Manager::getResource('order');
        $orders = $orderResource->getList(0, null)['data'];

        // ignore cancelled orders
        $orders = array_filter($orders, function ($item) {
            return $item['orderStatusId'] != -1;
        });

        $result = [];
        foreach ($orders as $order) {
            try {
                $result[] = $this->responseParser->parseOrder($orderResource->getOne($order['id']));
            } catch (Exception $e) {
                $this->logger->error($e->getMessage());
            }
        }

        return $result;
    }
}