<?php

namespace PlentymarketsAdapter\QueryBus\QueryHandler\Shop;

use PlentyConnector\Connector\QueryBus\Query\QueryInterface;
use PlentyConnector\Connector\QueryBus\Query\Shop\FetchAllShopsQuery;
use PlentyConnector\Connector\QueryBus\QueryHandler\QueryHandlerInterface;
use PlentymarketsAdapter\Client\ClientInterface;
use PlentymarketsAdapter\PlentymarketsAdapter;
use PlentymarketsAdapter\ResponseParser\ResponseParserInterface;

/**
 * Class FetchAllShopsQueryHandler
 */
class FetchAllShopsQueryHandler implements QueryHandlerInterface
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
     * FetchAllShopsQueryHandler constructor.
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
        return $query instanceof FetchAllShopsQuery &&
            $query->getAdapterName() === PlentymarketsAdapter::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(QueryInterface $query)
    {
        $shops = array_map(function ($shop) {
            return $this->responseParser->parse($shop);
        }, $this->client->request('GET', 'webstores'));

        return array_filter($shops);
    }
}
