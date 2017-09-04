<?php

namespace PlentymarketsAdapter\ServiceBus\QueryHandler\Stock;

use PlentyConnector\Connector\ServiceBus\Query\QueryInterface;
use PlentyConnector\Connector\ServiceBus\Query\Stock\FetchChangedStocksQuery;
use PlentyConnector\Connector\ServiceBus\QueryHandler\QueryHandlerInterface;
use PlentymarketsAdapter\PlentymarketsAdapter;
use PlentymarketsAdapter\ServiceBus\ChangedDateTimeTrait;

/**
 * Class FetchChangedStocksQueryHandler.
 */
class FetchChangedStocksQueryHandler implements QueryHandlerInterface
{
    use ChangedDateTimeTrait;

    /**
     * {@inheritdoc}
     */
    public function supports(QueryInterface $query)
    {
        return $query instanceof FetchChangedStocksQuery &&
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
