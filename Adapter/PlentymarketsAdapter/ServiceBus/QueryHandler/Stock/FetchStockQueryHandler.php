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
            $query->getAdapterName() === PlentymarketsAdapter::NAME &&
            $query->getObjectType() === Stock::TYPE &&
            $query->getQueryType() === QueryType::ONE;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(QueryInterface $query)
    {
        throw new InvalidArgumentException('unsupported operation');
    }
}
