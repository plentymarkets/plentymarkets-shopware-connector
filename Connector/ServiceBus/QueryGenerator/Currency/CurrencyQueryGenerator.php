<?php

namespace PlentyConnector\Connector\ServiceBus\QueryGenerator\Currency;

use PlentyConnector\Connector\ServiceBus\Query\Currency\FetchAllCurrenciesQuery;
use PlentyConnector\Connector\ServiceBus\Query\Currency\FetchChangedCurrenciesQuery;
use PlentyConnector\Connector\ServiceBus\Query\Currency\FetchCurrencyQuery;
use PlentyConnector\Connector\ServiceBus\QueryGenerator\QueryGeneratorInterface;
use PlentyConnector\Connector\TransferObject\Currency\Currency;

/**
 * Class CurrencyQueryGenerator
 */
class CurrencyQueryGenerator implements QueryGeneratorInterface
{
    /**
     * {@inheritdoc}
     */
    public function supports($transferObjectType)
    {
        return $transferObjectType === Currency::TYPE;
    }

    /**
     * {@inheritdoc}
     */
    public function generateFetchChangedQuery($adapterName)
    {
        return new FetchChangedCurrenciesQuery($adapterName);
    }

    /**
     * {@inheritdoc}
     */
    public function generateFetchAllQuery($adapterName)
    {
        return new FetchAllCurrenciesQuery($adapterName);
    }

    /**
     * {@inheritdoc}
     */
    public function generateFetchQuery($adapterName, $identifier)
    {
        return new FetchCurrencyQuery($adapterName, $identifier);
    }
}
