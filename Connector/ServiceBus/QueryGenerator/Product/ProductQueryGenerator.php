<?php

namespace PlentyConnector\Connector\ServiceBus\QueryGenerator\Product;

use PlentyConnector\Connector\ServiceBus\Query\Product\FetchAllProductsQuery;
use PlentyConnector\Connector\ServiceBus\Query\Product\FetchChangedProductsQuery;
use PlentyConnector\Connector\ServiceBus\Query\Product\FetchProductQuery;
use PlentyConnector\Connector\ServiceBus\QueryGenerator\QueryGeneratorInterface;
use PlentyConnector\Connector\TransferObject\Product\Product;

/**
 * Class ProductQueryGenerator
 */
class ProductQueryGenerator implements QueryGeneratorInterface
{
    /**
     * {@inheritdoc}
     */
    public function supports($transferObjectType)
    {
        return $transferObjectType === Product::TYPE;
    }

    /**
     * {@inheritdoc}
     */
    public function generateFetchChangedQuery($adapterName)
    {
        return new FetchChangedProductsQuery($adapterName);
    }

    /**
     * {@inheritdoc}
     */
    public function generateFetchAllQuery($adapterName)
    {
        return new FetchAllProductsQuery($adapterName);
    }

    /**
     * {@inheritdoc}
     */
    public function generateFetchQuery($adapterName, $identifier)
    {
        return new FetchProductQuery($adapterName, $identifier);
    }
}
