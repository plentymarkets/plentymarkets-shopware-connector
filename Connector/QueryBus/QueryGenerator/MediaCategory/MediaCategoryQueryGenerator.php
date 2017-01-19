<?php

namespace PlentyConnector\Connector\QueryBus\QueryGenerator\MediaCategory;

use PlentyConnector\Connector\QueryBus\Query\MediaCategory\FetchAllMediaCategoriesQuery;
use PlentyConnector\Connector\QueryBus\Query\MediaCategory\FetchChangedMediaCategoriesQuery;
use PlentyConnector\Connector\QueryBus\Query\MediaCategory\FetchMediaCategoryQuery;
use PlentyConnector\Connector\QueryBus\QueryGenerator\QueryGeneratorInterface;
use PlentyConnector\Connector\TransferObject\MediaCategory\MediaCategory;

/**
 * Class MediaCategoryQueryGenerator
 */
class MediaCategoryQueryGenerator implements QueryGeneratorInterface
{
    /**
     * {@inheritdoc}
     */
    public function supports($transferObjectType)
    {
        return $transferObjectType === MediaCategory::TYPE;
    }

    /**
     * {@inheritdoc}
     */
    public function generateFetchChangedQuery($adapterName)
    {
        return new FetchChangedMediaCategoriesQuery($adapterName);
    }

    /**
     * {@inheritdoc}
     */
    public function generateFetchAllQuery($adapterName)
    {
        return new FetchAllMediaCategoriesQuery($adapterName);
    }

    /**
     * {@inheritdoc}
     */
    public function generateFetchQuery($adapterName, $identifier)
    {
        return new FetchMediaCategoryQuery($adapterName, $identifier);
    }
}
