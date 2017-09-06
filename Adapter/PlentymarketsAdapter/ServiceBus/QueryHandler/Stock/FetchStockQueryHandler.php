<?php

namespace PlentymarketsAdapter\ServiceBus\QueryHandler\Stock;

use InvalidArgumentException;
use PlentyConnector\Connector\ServiceBus\Query\QueryInterface;
use PlentyConnector\Connector\ServiceBus\Query\Stock\FetchStockQuery;
use PlentyConnector\Connector\ServiceBus\QueryHandler\QueryHandlerInterface;
use PlentymarketsAdapter\Client\ClientInterface;
use PlentymarketsAdapter\PlentymarketsAdapter;
use PlentymarketsAdapter\ResponseParser\Product\Stock\StockResponseParserInterface;

/**
 * Class FetchStockQueryHandler.
 */
class FetchStockQueryHandler implements QueryHandlerInterface
{
    /**
     * {@inheritdoc}
     */
    public function supports(QueryInterface $query)
    {
        return $query instanceof FetchStockQuery &&
            $query->getAdapterName() === PlentymarketsAdapter::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(QueryInterface $query)
    {
        throw new InvalidArgumentException('unsupported operation');
    }
}
