<?php

namespace PlentyConnector\Connector\QueryBus\QueryGenerator\Country;

use PlentyConnector\Connector\QueryBus\Query\Country\FetchAllCountriesQuery;
use PlentyConnector\Connector\QueryBus\Query\Country\FetchChangedCountriesQuery;
use PlentyConnector\Connector\QueryBus\Query\Country\FetchCountryQuery;
use PlentyConnector\Connector\QueryBus\QueryGenerator\QueryGeneratorInterface;
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
        return $transferObjectType === Country::getType();
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
    public function generateFetchAllQuery($adapterName)
    {
        return new FetchAllCountriesQuery($adapterName);
    }

    /**
     * {@inheritdoc}
     */
    public function generateFetchQuery($adapterName, $identifier)
    {
        return new FetchCountryQuery($adapterName, $identifier);
    }
}
