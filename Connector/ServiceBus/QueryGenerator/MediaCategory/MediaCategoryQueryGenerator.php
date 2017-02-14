<?php

namespace PlentyConnector\Connector\ServiceBus\QueryGenerator\MediaCategory;

use PlentyConnector\Connector\ServiceBus\Query\MediaCategory\FetchAllMediaCategoriesQuery;
use PlentyConnector\Connector\ServiceBus\Query\MediaCategory\FetchChangedMediaCategoriesQuery;
use PlentyConnector\Connector\ServiceBus\Query\MediaCategory\FetchMediaCategoryQuery;
use PlentyConnector\Connector\ServiceBus\QueryGenerator\QueryGeneratorInterface;
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
    public function generateFetchAllQuery($adapterName)
    {
        return new FetchAllMediaCategoriesQuery($adapterName);
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
    public function generateFetchQuery($adapterName, $identifier)
    {
        return new FetchMediaCategoryQuery($adapterName, $identifier);
    }
}
