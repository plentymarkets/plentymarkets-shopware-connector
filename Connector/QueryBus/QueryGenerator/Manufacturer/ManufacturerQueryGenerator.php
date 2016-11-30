<?php

namespace PlentyConnector\Connector\QueryBus\QueryGenerator\Manufacturer;

use PlentyConnector\Connector\QueryBus\Query\Manufacturer\FetchChangedManufacturerQuery;
use PlentyConnector\Connector\QueryBus\Query\Manufacturer\FetchAllManufacturerQuery;
use PlentyConnector\Connector\QueryBus\Query\Manufacturer\FetchManufacturerQuery;
use PlentyConnector\Connector\QueryBus\Query\QueryInterface;
use PlentyConnector\Connector\QueryBus\QueryGenerator\QueryGeneratorInterface;
use PlentyConnector\Connector\TransferObject\TransferObjectType;

/**
 * Class ManufacturerQueryGenerator
 */
class ManufacturerQueryGenerator implements QueryGeneratorInterface
{
    /**
     * @param string $transferObjectType
     *
     * @return boolean
     */
    public function supports($transferObjectType)
    {
        return $transferObjectType === TransferObjectType::MANUFACTURER;
    }

    /**
     * @param string $adapterName
     *
     * @return QueryInterface
     */
    public function generateFetchChangedQuery($adapterName)
    {
        return new FetchChangedManufacturerQuery($adapterName);
    }

    /**
     * @param string $adapterName
     *
     * @return QueryInterface
     */
    public function generateFetchAllQuery($adapterName)
    {
        return new FetchAllManufacturerQuery($adapterName);
    }

    /**
     * @param string $adapterName
     * @param string $identifier
     *
     * @return QueryInterface
     */
    public function generateFetchQuery($adapterName, $identifier)
    {
        return new FetchManufacturerQuery($adapterName, $identifier);
    }
}
