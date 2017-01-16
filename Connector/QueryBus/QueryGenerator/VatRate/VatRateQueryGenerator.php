<?php

namespace PlentyConnector\Connector\QueryBus\QueryGenerator\VatRate;

use PlentyConnector\Connector\QueryBus\Query\VatRate\FetchAllVatRatesQuery;
use PlentyConnector\Connector\QueryBus\Query\VatRate\FetchChangedVatRatesQuery;
use PlentyConnector\Connector\QueryBus\Query\VatRate\FetchVatRateQuery;
use PlentyConnector\Connector\QueryBus\QueryGenerator\QueryGeneratorInterface;
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
    public function generateFetchChangedQuery($adapterName)
    {
        return new FetchChangedVatRatesQuery($adapterName);
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
    public function generateFetchQuery($adapterName, $identifier)
    {
        return new FetchVatRateQuery($adapterName, $identifier);
    }
}
