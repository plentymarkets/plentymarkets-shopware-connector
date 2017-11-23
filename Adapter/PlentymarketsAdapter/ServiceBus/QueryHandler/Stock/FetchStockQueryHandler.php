<?php

namespace PlentymarketsAdapter\ServiceBus\QueryHandler\Stock;

use InvalidArgumentException;
use PlentyConnector\Connector\ServiceBus\Query\FetchTransferObjectQuery;
use PlentyConnector\Connector\ServiceBus\Query\QueryInterface;
use PlentyConnector\Connector\ServiceBus\QueryHandler\QueryHandlerInterface;
use PlentyConnector\Connector\ServiceBus\QueryType;
use PlentyConnector\Connector\TransferObject\Product\Stock\Stock;
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
        return $query instanceof FetchTransferObjectQuery &&
            PlentymarketsAdapter::NAME === $query->getAdapterName() &&
            Stock::TYPE === $query->getObjectType() &&
            QueryType::ONE === $query->getQueryType();
    }

    /**
     * {@inheritdoc}
     */
    public function handle(QueryInterface $query)
    {
        throw new InvalidArgumentException('unsupported operation');
    }
}
