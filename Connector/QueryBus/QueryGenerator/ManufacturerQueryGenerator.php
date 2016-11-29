<?php

namespace PlentyConnector\Connector\QueryBus\QueryGenerator;

use PlentyConnector\Connector\QueryBus\Query\Manufacturer\FetchChangedManufacturerQuery;
use PlentyConnector\Connector\QueryBus\Query\Manufacturer\FetchAllManufacturerQuery;
use PlentyConnector\Connector\QueryBus\Query\Manufacturer\FetchManufacturerQuery;
use PlentyConnector\Connector\QueryBus\Query\QueryInterface;
use PlentyConnector\Connector\TransferObject\TransferObjectType;

/**
 * Class ManufacturerQueryGenerator
 */
class ManufacturerQueryQueryGenerator implements QueryGeneratorInterface
{
    /**
     * @param string $transferObjectName
     *
     * @return boolean
     */
    public function supports($transferObjectName)
    {
        return $transferObjectName === TransferObjectType::MANUFACTURER;
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
