<?php

namespace PlentyConnector\Connector\ServiceBus\QueryGenerator\Stock;

use PlentyConnector\Connector\ServiceBus\Query\Stock\FetchAllStocksQuery;
use PlentyConnector\Connector\ServiceBus\Query\Stock\FetchChangedStocksQuery;
use PlentyConnector\Connector\ServiceBus\Query\Stock\FetchStockQuery;
use PlentyConnector\Connector\ServiceBus\QueryGenerator\QueryGeneratorInterface;
use PlentyConnector\Connector\TransferObject\Product\Stock\Stock;

/**
 * Class StockQueryGenerator
 */
class StockQueryGenerator implements QueryGeneratorInterface
{
    /**
     * {@inheritdoc}
     */
    public function supports($transferObjectType)
    {
        return $transferObjectType === Stock::TYPE;
    }

    /**
     * {@inheritdoc}
     */
    public function generateFetchAllQuery($adapterName)
    {
        return new FetchAllStocksQuery($adapterName);
    }

    /**
     * {@inheritdoc}
     */
    public function generateFetchChangedQuery($adapterName)
    {
        return new FetchChangedStocksQuery($adapterName);
    }

    /**
     * {@inheritdoc}
     */
    public function generateFetchQuery($adapterName, $identifier)
    {
        return new FetchStockQuery($adapterName, $identifier);
    }
}
