<?php

namespace PlentymarketsAdapter\QueryBus\QueryHandler\OrderStatus;

use PlentyConnector\Connector\QueryBus\Query\OrderStatus\FetchAllOrderStatusesQuery;
use PlentyConnector\Connector\QueryBus\Query\QueryInterface;
use PlentyConnector\Connector\QueryBus\QueryHandler\QueryHandlerInterface;
use PlentymarketsAdapter\Client\ClientInterface;
use PlentymarketsAdapter\PlentymarketsAdapter;
use PlentymarketsAdapter\ResponseParser\ResponseParserInterface;

/**
 * Class FetchAllOrderStatusesQueryHandler
 */
class FetchAllOrderStatusesQueryHandler implements QueryHandlerInterface
{
    /**
     * @var ClientInterface
     */
    private $client;

    /**
     * @var ResponseParserInterface
     */
    private $responseParser;

    /**
     * FetchAllOrderStatusesQueryHandler constructor.
     *
     * @param ClientInterface $client
     * @param ResponseParserInterface $responseParser
     */
    public function __construct(
        ClientInterface $client,
        ResponseParserInterface $responseParser
    ) {
        $this->client = $client;
        $this->responseParser = $responseParser;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(QueryInterface $query)
    {
        return $query instanceof FetchAllOrderStatusesQuery &&
            $query->getAdapterName() === PlentymarketsAdapter::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(QueryInterface $query)
    {
        $statusArray = array_map(function ($orderStatus) {
            return $this->responseParser->parse($orderStatus);
        }, $this->client->request('GET', 'orders/statuses', ['with' => 'names']));

        return array_filter($statusArray);
    }
}
