<?php

namespace PlentymarketsAdapter\ServiceBus\QueryHandler\Order;

use PlentyConnector\Connector\ServiceBus\Query\Order\FetchAllOrdersQuery;
use PlentyConnector\Connector\ServiceBus\Query\QueryInterface;
use PlentyConnector\Connector\ServiceBus\QueryHandler\QueryHandlerInterface;
use PlentymarketsAdapter\Client\ClientInterface;
use PlentymarketsAdapter\PlentymarketsAdapter;
use PlentymarketsAdapter\ResponseParser\Order\OrderResponseParserInterface;

/**
 * Class FetchAllOrdersQueryHandler
 */
class FetchAllOrdersQueryHandler implements QueryHandlerInterface
{
    /**
     * @var ClientInterface
     */
    private $client;

    /**
     * @var OrderResponseParserInterface
     */
    private $orderResponseParser;

    /**
     * FetchAllOrdersQueryHandler constructor.
     *
     * @param ClientInterface $client
     * @param OrderResponseParserInterface $orderResponseParser
     */
    public function __construct(
        ClientInterface $client,
        OrderResponseParserInterface $orderResponseParser
    ) {
        $this->client = $client;
        $this->orderResponseParser = $orderResponseParser;
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
        return [];
    }
}
