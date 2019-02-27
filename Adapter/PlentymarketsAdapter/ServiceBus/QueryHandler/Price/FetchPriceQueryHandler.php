<?php

namespace PlentymarketsAdapter\ServiceBus\QueryHandler\Price;

use InvalidArgumentException;
use PlentymarketsAdapter\PlentymarketsAdapter;
use SystemConnector\ServiceBus\Query\FetchTransferObjectQuery;
use SystemConnector\ServiceBus\Query\QueryInterface;
use SystemConnector\ServiceBus\QueryHandler\QueryHandlerInterface;
use SystemConnector\ServiceBus\QueryType;
use SystemConnector\TransferObject\Product\Price\Price;

class FetchPriceQueryHandler implements QueryHandlerInterface
{
    /**
     * {@inheritdoc}
     */
    public function supports(QueryInterface $query)
    {
        return $query instanceof FetchTransferObjectQuery &&
            $query->getAdapterName() === PlentymarketsAdapter::NAME &&
            $query->getObjectType() === Price::TYPE &&
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
