<?php

namespace PlentyConnector\Connector\ServiceBus\QueryGenerator\Unit;

use PlentyConnector\Connector\ServiceBus\Query\Unit\FetchAllUnitsQuery;
use PlentyConnector\Connector\ServiceBus\Query\Unit\FetchChangedUnitsQuery;
use PlentyConnector\Connector\ServiceBus\Query\Unit\FetchUnitQuery;
use PlentyConnector\Connector\ServiceBus\QueryGenerator\QueryGeneratorInterface;
use PlentyConnector\Connector\TransferObject\Unit\Unit;

/**
 * Class UnitQueryGenerator
 */
class UnitQueryGenerator implements QueryGeneratorInterface
{
    /**
     * {@inheritdoc}
     */
    public function supports($transferObjectType)
    {
        return $transferObjectType === Unit::TYPE;
    }

    /**
     * {@inheritdoc}
     */
    public function generateFetchAllQuery($adapterName)
    {
        return new FetchAllUnitsQuery($adapterName);
    }

    /**
     * {@inheritdoc}
     */
    public function generateFetchChangedQuery($adapterName)
    {
        return new FetchChangedUnitsQuery($adapterName);
    }

    /**
     * {@inheritdoc}
     */
    public function generateFetchQuery($adapterName, $identifier)
    {
        return new FetchUnitQuery($adapterName, $identifier);
    }
}
