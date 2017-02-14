<?php

namespace PlentyConnector\Connector\ServiceBus\QueryGenerator\Country;

use PlentyConnector\Connector\ServiceBus\Query\Country\FetchAllCountriesQuery;
use PlentyConnector\Connector\ServiceBus\Query\Country\FetchChangedCountriesQuery;
use PlentyConnector\Connector\ServiceBus\Query\Country\FetchCountryQuery;
use PlentyConnector\Connector\ServiceBus\QueryGenerator\QueryGeneratorInterface;
use PlentyConnector\Connector\TransferObject\Country\Country;

/**
 * Class CountryQueryGenerator
 */
class CountryQueryGenerator implements QueryGeneratorInterface
{
    /**
     * {@inheritdoc}
     */
    public function supports($transferObjectType)
    {
        return $transferObjectType === Country::TYPE;
    }

    /**
     * {@inheritdoc}
     */
    public function generateFetchAllQuery($adapterName)
    {
        return new FetchAllCountriesQuery($adapterName);
    }

    /**
     * {@inheritdoc}
     */
    public function generateFetchChangedQuery($adapterName)
    {
        return new FetchChangedCountriesQuery($adapterName);
    }

    /**
     * {@inheritdoc}
     */
    public function generateFetchQuery($adapterName, $identifier)
    {
        return new FetchCountryQuery($adapterName, $identifier);
    }
}
