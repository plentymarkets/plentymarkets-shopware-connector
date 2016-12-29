<?php

namespace PlentyConnector\Connector\QueryBus\QueryGenerator\Manufacturer;

use PlentyConnector\Connector\QueryBus\Query\Manufacturer\FetchAllManufacturersQuery;
use PlentyConnector\Connector\QueryBus\Query\Manufacturer\FetchChangedManufacturersQuery;
use PlentyConnector\Connector\QueryBus\Query\Manufacturer\FetchManufacturerQuery;
use PlentyConnector\Connector\QueryBus\QueryGenerator\QueryGeneratorInterface;
use PlentyConnector\Connector\TransferObject\Manufacturer\Manufacturer;

/**
 * Class ManufacturerQueryGenerator
 */
class ManufacturerQueryGenerator implements QueryGeneratorInterface
{
    /**
     * {@inheritdoc}
     */
    public function supports($transferObjectType)
    {
        return $transferObjectType === Manufacturer::getType();
    }

    /**
     * {@inheritdoc}
     */
    public function generateFetchChangedQuery($adapterName)
    {
        return new FetchChangedManufacturersQuery($adapterName);
    }

    /**
     * {@inheritdoc}
     */
    public function generateFetchAllQuery($adapterName)
    {
        return new FetchAllManufacturersQuery($adapterName);
    }

    /**
     * {@inheritdoc}
     */
    public function generateFetchQuery($adapterName, $identifier)
    {
        return new FetchManufacturerQuery($adapterName, $identifier);
    }
}
