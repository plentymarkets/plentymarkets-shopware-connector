<?php

namespace PlentymarketsAdapter\ServiceBus\QueryHandler\Order;

use PlentyConnector\Connector\ServiceBus\Query\Order\FetchAllOrdersQuery;
use PlentyConnector\Connector\ServiceBus\Query\QueryInterface;
use PlentyConnector\Connector\ServiceBus\QueryHandler\QueryHandlerInterface;
use PlentymarketsAdapter\PlentymarketsAdapter;
use PlentymarketsAdapter\ReadApi\Order\Order;
use PlentymarketsAdapter\ResponseParser\Order\OrderResponseParserInterface;
use Psr\Log\LoggerInterface;

/**
 * Class FetchAllOrdersQueryHandler
 */
class FetchAllOrdersQueryHandler implements QueryHandlerInterface
{
    /**
     * @var Order
     */
    private $api;

    /**
     * @var OrderResponseParserInterface
     */
    private $responseParser;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * FetchAllOrdersQueryHandler constructor.
     *
     * @param Order $api
     * @param OrderResponseParserInterface $responseParser
     * @param LoggerInterface $logger
     */
    public function __construct(
        Order $api,
        OrderResponseParserInterface $responseParser,
        LoggerInterface $logger
    ) {
        $this->api = $api;
        $this->responseParser = $responseParser;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(QueryInterface $query)
    {
        return $query instanceof FetchAllOrdersQuery &&
            $query->getAdapterName() === PlentymarketsAdapter::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(QueryInterface $query)
    {
        $orders = $this->api->findAll();

        $parsedElements = [];
        foreach ($orders as $order) {
            try {
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
