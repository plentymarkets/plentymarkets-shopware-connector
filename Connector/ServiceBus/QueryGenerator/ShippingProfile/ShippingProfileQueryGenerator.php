<?php

namespace PlentyConnector\Connector\ServiceBus\QueryGenerator\ShippingProfile;

use PlentyConnector\Connector\ServiceBus\Query\ShippingProfile\FetchAllShippingProfilesQuery;
use PlentyConnector\Connector\ServiceBus\Query\ShippingProfile\FetchChangedShippingProfilesQuery;
use PlentyConnector\Connector\ServiceBus\Query\ShippingProfile\FetchShippingProfileQuery;
use PlentyConnector\Connector\ServiceBus\QueryGenerator\QueryGeneratorInterface;
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
    public function generateFetchAllQuery($adapterName)
    {
        return new FetchAllShippingProfilesQuery($adapterName);
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
    public function generateFetchQuery($adapterName, $identifier)
    {
        return new FetchShippingProfileQuery($adapterName, $identifier);
    }
}
