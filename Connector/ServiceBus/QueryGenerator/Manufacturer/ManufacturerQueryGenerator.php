<?php

namespace PlentyConnector\Connector\ServiceBus\QueryGenerator\Manufacturer;

use PlentyConnector\Connector\ServiceBus\Query\Manufacturer\FetchAllManufacturersQuery;
use PlentyConnector\Connector\ServiceBus\Query\Manufacturer\FetchChangedManufacturersQuery;
use PlentyConnector\Connector\ServiceBus\Query\Manufacturer\FetchManufacturerQuery;
use PlentyConnector\Connector\ServiceBus\QueryGenerator\QueryGeneratorInterface;
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
        return $transferObjectType === Manufacturer::TYPE;
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
    public function generateFetchChangedQuery($adapterName)
    {
        return new FetchChangedManufacturersQuery($adapterName);
    }

    /**
     * {@inheritdoc}
     */
    public function generateFetchQuery($adapterName, $identifier)
    {
        return new FetchManufacturerQuery($adapterName, $identifier);
    }
}
