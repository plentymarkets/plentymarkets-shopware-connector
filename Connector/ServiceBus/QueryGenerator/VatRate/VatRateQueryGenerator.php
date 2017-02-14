<?php

namespace PlentyConnector\Connector\ServiceBus\QueryGenerator\VatRate;

use PlentyConnector\Connector\ServiceBus\Query\VatRate\FetchAllVatRatesQuery;
use PlentyConnector\Connector\ServiceBus\Query\VatRate\FetchChangedVatRatesQuery;
use PlentyConnector\Connector\ServiceBus\Query\VatRate\FetchVatRateQuery;
use PlentyConnector\Connector\ServiceBus\QueryGenerator\QueryGeneratorInterface;
use PlentyConnector\Connector\TransferObject\VatRate\VatRate;

/**
 * Class VatRateQueryGenerator
 */
class VatRateQueryGenerator implements QueryGeneratorInterface
{
    /**
     * {@inheritdoc}
     */
    public function supports($transferObjectType)
    {
        return $transferObjectType === VatRate::TYPE;
    }

    /**
     * {@inheritdoc}
     */
    public function generateFetchAllQuery($adapterName)
    {
        return new FetchAllVatRatesQuery($adapterName);
    }

    /**
     * {@inheritdoc}
     */
    public function generateFetchChangedQuery($adapterName)
    {
        return new FetchChangedVatRatesQuery($adapterName);
    }

    /**
     * {@inheritdoc}
     */
    public function generateFetchQuery($adapterName, $identifier)
    {
        return new FetchVatRateQuery($adapterName, $identifier);
    }
}
