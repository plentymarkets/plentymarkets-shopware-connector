<?php


namespace PlentyConnector\Connector\QueryBus\QueryGenerator;


use PlentyConnector\Connector\QueryBus\Query\QueryInterface;

/**
 * Interface GeneratorInterface
 */
interface QueryGeneratorInterface
{
    /**
     * @param string $transferObjectName
     *
     * @return boolean
     */
    public function supports($transferObjectName);

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
