<?php

namespace PlentyConnector\Connector\QueryBus\QueryGenerator\Product;

use PlentyConnector\Connector\QueryBus\Query\Product\FetchAllProductsQuery;
use PlentyConnector\Connector\QueryBus\Query\Product\FetchChangedProductsQuery;
use PlentyConnector\Connector\QueryBus\Query\Product\FetchProductQuery;
use PlentyConnector\Connector\QueryBus\QueryGenerator\QueryGeneratorInterface;
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
        return $transferObjectType === Product::getType();
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
