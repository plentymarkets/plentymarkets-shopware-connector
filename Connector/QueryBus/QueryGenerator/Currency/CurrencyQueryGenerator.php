<?php

namespace PlentyConnector\Connector\QueryBus\QueryGenerator\Currency;

use PlentyConnector\Connector\QueryBus\Query\Currency\FetchAllCurrenciesQuery;
use PlentyConnector\Connector\QueryBus\Query\Currency\FetchChangedCurrenciesQuery;
use PlentyConnector\Connector\QueryBus\Query\Currency\FetchCurrencyQuery;
use PlentyConnector\Connector\QueryBus\QueryGenerator\QueryGeneratorInterface;
use PlentyConnector\Connector\TransferObject\TransferObjectType;

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
        return $transferObjectType === TransferObjectType::CURRENCY;
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
