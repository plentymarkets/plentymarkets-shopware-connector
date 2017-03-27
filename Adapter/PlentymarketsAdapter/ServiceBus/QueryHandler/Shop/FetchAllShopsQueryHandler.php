<?php

namespace PlentymarketsAdapter\ServiceBus\QueryHandler\Shop;

use PlentyConnector\Connector\ServiceBus\Query\QueryInterface;
use PlentyConnector\Connector\ServiceBus\Query\Shop\FetchAllShopsQuery;
use PlentyConnector\Connector\ServiceBus\QueryHandler\QueryHandlerInterface;
use PlentymarketsAdapter\Client\ClientInterface;
use PlentymarketsAdapter\PlentymarketsAdapter;
use PlentymarketsAdapter\ResponseParser\Shop\ShopResponseParserInterface;

/**
 * Class FetchAllShopsQueryHandler.
 */
class FetchAllShopsQueryHandler implements QueryHandlerInterface
{
    /**
     * @var ClientInterface
     */
    private $client;

    /**
     * @var ShopResponseParserInterface
     */
    private $responseParser;

    /**
     * FetchAllShopsQueryHandler constructor.
     *
     * @param ClientInterface             $client
     * @param ShopResponseParserInterface $responseParser
     */
    public function __construct(
        ClientInterface $client,
        ShopResponseParserInterface $responseParser
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
