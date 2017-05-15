<?php

namespace PlentyConnector\Components\Bundle\QueryGenerator;

use PlentyConnector\Components\Bundle\Query\FetchAllBundlesQuery;
use PlentyConnector\Components\Bundle\Query\FetchBundleQuery;
use PlentyConnector\Components\Bundle\Query\FetchChangedBundlesQuery;
use PlentyConnector\Components\Bundle\TransferObject\Bundle;
use PlentyConnector\Connector\ServiceBus\QueryGenerator\QueryGeneratorInterface;

/**
 * Class BundleQueryGenerator
 */
class BundleQueryGenerator implements QueryGeneratorInterface
{
    /**
     * {@inheritdoc}
     */
    public function supports($transferObjectType)
    {
        return $transferObjectType === Bundle::TYPE;
    }

    /**
     * {@inheritdoc}
     */
    public function generateFetchAllQuery($adapterName)
    {
        return new FetchAllBundlesQuery($adapterName);
    }

    /**
     * {@inheritdoc}
     */
    public function generateFetchChangedQuery($adapterName)
    {
        return new FetchChangedBundlesQuery($adapterName);
    }

    /**
     * {@inheritdoc}
     */
    public function generateFetchQuery($adapterName, $identifier)
    {
        return new FetchBundleQuery($adapterName, $identifier);
    }
}
