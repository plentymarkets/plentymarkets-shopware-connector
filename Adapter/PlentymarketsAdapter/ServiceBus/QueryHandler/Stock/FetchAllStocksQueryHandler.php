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
     * {@inheritdoc}
     */
    public function supports(QueryInterface $query)
    {
        return $query instanceof FetchAllStocksQuery &&
            $query->getAdapterName() === PlentymarketsAdapter::NAME;
    }

    /**
     * TODO: finalize
     *
     * {@inheritdoc}
     */
    public function handle(QueryInterface $query)
    {
        /**
         * @var ClientInterface $client
         */
        $client = Shopware()->Container()->get('plentmarkets_adapter.client');

        $stocks = iterator_to_array($client->getIterator('stockmanagement/stock'));

        $groupedStock = [];
        foreach ($stocks as $stock) {
            $groupedStock[$stock['variationId']]['id'] = $stock['variationId'];
            $groupedStock[$stock['variationId']]['stock'][] = $stock;
        }

        /**
         * @var StockResponseParserInterface $stockResponseParser
         */
        $stockResponseParser = Shopware()->Container()->get('plentmarkets_adapter.response_parser.stock');

        foreach ($groupedStock as $variation) {
            $transferObjects = $stockResponseParser->parse($variation);

            foreach ($transferObjects as $object) {
                yield $object;
            }
        }
    }
}
