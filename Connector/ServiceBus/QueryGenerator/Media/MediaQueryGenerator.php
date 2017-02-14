<?php

namespace PlentyConnector\Connector\ServiceBus\QueryGenerator\Media;

use PlentyConnector\Connector\ServiceBus\Query\Media\FetchAllMediaQuery;
use PlentyConnector\Connector\ServiceBus\Query\Media\FetchChangedMediaQuery;
use PlentyConnector\Connector\ServiceBus\Query\Media\FetchMediaQuery;
use PlentyConnector\Connector\ServiceBus\QueryGenerator\QueryGeneratorInterface;
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
    public function generateFetchAllQuery($adapterName)
    {
        return new FetchAllMediaQuery($adapterName);
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
    public function generateFetchQuery($adapterName, $identifier)
    {
        return new FetchMediaQuery($adapterName, $identifier);
    }
}
