<?php

namespace PlentyConnector\Connector\QueryBus\QueryGenerator\ShippingProfile;

use PlentyConnector\Connector\QueryBus\Query\ShippingProfile\FetchAllShippingProfilesQuery;
use PlentyConnector\Connector\QueryBus\Query\ShippingProfile\FetchChangedShippingProfilesQuery;
use PlentyConnector\Connector\QueryBus\Query\ShippingProfile\FetchShippingProfileQuery;
use PlentyConnector\Connector\QueryBus\QueryGenerator\QueryGeneratorInterface;
use PlentyConnector\Connector\TransferObject\ShippingProfile\ShippingProfile;

/**
 * Class ShippingProfileQueryGenerator
 */
class ShippingProfileQueryGenerator implements QueryGeneratorInterface
{
    /**
     * {@inheritdoc}
     */
    public function supports($transferObjectType)
    {
        return $transferObjectType === ShippingProfile::TYPE;
    }

    /**
     * {@inheritdoc}
     */
    public function generateFetchChangedQuery($adapterName)
    {
        return new FetchChangedShippingProfilesQuery($adapterName);
    }

    /**
     * {@inheritdoc}
     */
    public function generateFetchAllQuery($adapterName)
    {
        return new FetchAllShippingProfilesQuery($adapterName);
    }

    /**
     * {@inheritdoc}
     */
    public function generateFetchQuery($adapterName, $identifier)
    {
        return new FetchShippingProfileQuery($adapterName, $identifier);
    }
}
