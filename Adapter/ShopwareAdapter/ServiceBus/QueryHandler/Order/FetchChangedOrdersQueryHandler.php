<?php

namespace ShopwareAdapter\ServiceBus\QueryHandler\Order;

use PlentyConnector\Connector\ServiceBus\Query\Order\FetchChangedOrdersQuery;
use PlentyConnector\Connector\ServiceBus\Query\QueryInterface;
use PlentyConnector\Connector\ServiceBus\QueryHandler\QueryHandlerInterface;
use Psr\Log\LoggerInterface;
use ShopwareAdapter\DataProvider\Order\OrderDataProviderInterface;
use ShopwareAdapter\ResponseParser\Order\OrderResponseParserInterface;
use ShopwareAdapter\ShopwareAdapter;

/**
 * Class FetchChangedOrdersQueryHandler
 */
class FetchChangedOrdersQueryHandler implements QueryHandlerInterface
{
    /**
     * @var OrderResponseParserInterface
     */
    private $responseParser;

    /**
     * @var OrderDataProviderInterface
     */
    private $dataProvider;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * FetchChangedOrdersQueryHandler constructor.
     *
     * @param OrderResponseParserInterface $responseParser
     * @param OrderDataProviderInterface $dataProvider
     * @param LoggerInterface $logger
     */
    public function __construct(
        OrderResponseParserInterface $responseParser,
        OrderDataProviderInterface $dataProvider,
        LoggerInterface $logger
    ) {
        $this->responseParser = $responseParser;
        $this->dataProvider = $dataProvider;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(QueryInterface $event)
    {
        return $event instanceof FetchChangedOrdersQuery &&
            $event->getAdapterName() === ShopwareAdapter::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(QueryInterface $query)
    {
        $orders = $this->dataProvider->getOpenOrders();

        $parsedElements = [];
        foreach ($orders as $order) {
            try {
                $order = $this->dataProvider->getOrderDetails($order['id']);

                $result = $this->responseParser->parse($order);
            } catch (Exception $exception) {
                $this->logger->error($exception->getMessage());

                $result = null;
            }

            if (empty($result)) {
                continue;
            }

            $parsedElements = array_filter($result);

            foreach ($parsedElements as $parsedElement) {
                $parsedElements[] = $parsedElement;
            }
        }

        return $parsedElements;
    }
}
