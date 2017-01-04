<?php

namespace PlentyConnector\Connector\QueryBus\QueryGenerator\Category;

use PlentyConnector\Connector\QueryBus\Query\Category\FetchAllCategoriesQuery;
use PlentyConnector\Connector\QueryBus\Query\Category\FetchCategoryQuery;
use PlentyConnector\Connector\QueryBus\Query\Category\FetchChangedCategoriesQuery;
use PlentyConnector\Connector\QueryBus\QueryGenerator\QueryGeneratorInterface;
use PlentyConnector\Connector\TransferObject\Category\Category;

/**
 * Class CategoryQueryGenerator
 */
class CategoryQueryGenerator implements QueryGeneratorInterface
{
    /**
     * {@inheritdoc}
     */
    public function supports($transferObjectType)
    {
        return $transferObjectType === Category::getType();
    }

    /**
     * {@inheritdoc}
     */
    public function generateFetchChangedQuery($adapterName)
    {
        return new FetchChangedCategoriesQuery($adapterName);
    }

    /**
     * {@inheritdoc}
     */
    public function generateFetchAllQuery($adapterName)
    {
        return new FetchAllCategoriesQuery($adapterName);
    }

    /**
     * {@inheritdoc}
     */
    public function generateFetchQuery($adapterName, $identifier)
    {
        return new FetchCategoryQuery($adapterName, $identifier);
    }
}
