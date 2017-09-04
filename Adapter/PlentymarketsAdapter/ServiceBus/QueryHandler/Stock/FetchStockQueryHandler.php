<?php

namespace PlentymarketsAdapter\ServiceBus\QueryHandler\Stock;

use PlentyConnector\Connector\ServiceBus\Query\QueryInterface;
use PlentyConnector\Connector\ServiceBus\Query\Stock\FetchStockQuery;
use PlentyConnector\Connector\ServiceBus\QueryHandler\QueryHandlerInterface;
use PlentymarketsAdapter\PlentymarketsAdapter;

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
     * TODO: finalize
     *
     * {@inheritdoc}
     */
    public function handle(QueryInterface $query)
    {
    }
}
