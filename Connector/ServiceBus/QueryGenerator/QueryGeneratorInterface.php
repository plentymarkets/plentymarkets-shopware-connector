<?php


namespace PlentyConnector\Connector\ServiceBus\QueryGenerator;


use PlentyConnector\Connector\ServiceBus\Query\QueryInterface;
use PlentyConnector\Connector\TransferObject\TransferObjectInterface;

/**
 * Interface QueryGeneratorInterface
 */
interface QueryGeneratorInterface
{
    /**
     * @param string $transferObjectType
     *
     * @return bool
     */
    public function supports($transferObjectType);

    /**
     * @param string $adapterName
     *
     * @return QueryInterface
     */
    public function generateFetchChangedQuery($adapterName);

    /**
     * @param string $adapterName
     *
     * @return QueryInterface
     */
    public function generateFetchAllQuery($adapterName);

    /**
     * @param string $adapterName
     * @param string $identifier
     *
     * @return QueryInterface
     */
    public function generateFetchQuery($adapterName, $identifier);
}
