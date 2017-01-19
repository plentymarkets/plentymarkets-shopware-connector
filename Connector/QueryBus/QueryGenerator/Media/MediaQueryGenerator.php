<?php

namespace PlentyConnector\Connector\QueryBus\QueryGenerator\Media;

use PlentyConnector\Connector\QueryBus\Query\Media\FetchAllMediaQuery;
use PlentyConnector\Connector\QueryBus\Query\Media\FetchChangedMediaQuery;
use PlentyConnector\Connector\QueryBus\Query\Media\FetchMediaQuery;
use PlentyConnector\Connector\QueryBus\QueryGenerator\QueryGeneratorInterface;
use PlentyConnector\Connector\TransferObject\Media\Media;

/**
 * Class MediaQueryGenerator
 */
class MediaQueryGenerator implements QueryGeneratorInterface
{
    /**
     * {@inheritdoc}
     */
    public function supports($transferObjectType)
    {
        return $transferObjectType === Media::TYPE;
    }

    /**
     * {@inheritdoc}
     */
    public function generateFetchChangedQuery($adapterName)
    {
        return new FetchChangedMediaQuery($adapterName);
    }

    /**
     * {@inheritdoc}
     */
    public function generateFetchAllQuery($adapterName)
    {
        return new FetchAllMediaQuery($adapterName);
    }

    /**
     * {@inheritdoc}
     */
    public function generateFetchQuery($adapterName, $identifier)
    {
        return new FetchMediaQuery($adapterName, $identifier);
    }
}
