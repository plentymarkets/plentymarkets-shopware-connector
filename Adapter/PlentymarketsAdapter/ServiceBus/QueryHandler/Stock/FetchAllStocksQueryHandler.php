<?php

namespace PlentymarketsAdapter\ServiceBus\QueryHandler\Stock;

use PlentyConnector\Connector\ServiceBus\Query\QueryInterface;
use PlentyConnector\Connector\ServiceBus\Query\Stock\FetchAllStocksQuery;
use PlentyConnector\Connector\ServiceBus\QueryHandler\QueryHandlerInterface;
use PlentymarketsAdapter\Client\ClientInterface;
use PlentymarketsAdapter\PlentymarketsAdapter;
use PlentymarketsAdapter\ResponseParser\Product\Stock\StockResponseParserInterface;

/**
 * Class FetchAllStocksQueryHandler
 */
class FetchAllStocksQueryHandler implements QueryHandlerInterface
{
    /**
     * @var ClientInterface
     */
    private $client;

    /**
     * @var StockResponseParserInterface
     */
    private $responseParser;

    /**
     * FetchAllStocksQueryHandler constructor.
     *
     * @param ClientInterface              $client
     * @param StockResponseParserInterface $responseParser
     */
    public function __construct(ClientInterface $client, StockResponseParserInterface $responseParser)
    {
        $this->client = $client;
        $this->responseParser = $responseParser;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(QueryInterface $query)
    {
        return $query instanceof FetchAllStocksQuery &&
            $query->getAdapterName() === PlentymarketsAdapter::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(QueryInterface $query)
    {
        $stocks = $this->client->getIterator('stockmanagement/stock');

        $groupedStock = [];
        foreach ($stocks as $stock) {
            $groupedStock[$stock['variationId']]['id'] = $stock['variationId'];
            $groupedStock[$stock['variationId']]['stock'][] = $stock;
        }

        foreach ($groupedStock as $variation) {
            $transferObjects = $this->responseParser->parse($variation);

            foreach ($transferObjects as $object) {
                yield $object;
            }
        }
    }
}
