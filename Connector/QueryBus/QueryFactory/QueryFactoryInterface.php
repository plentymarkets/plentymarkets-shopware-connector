<?php

namespace PlentyConnector\Connector\QueryBus\QueryFactory;

use PlentyConnector\Connector\QueryBus\Query\QueryInterface;
use PlentyConnector\Connector\QueryBus\QueryGenerator\QueryGeneratorInterface;

/**
 * Class QueryFactoryInterface.
 */
interface QueryFactoryInterface
{
    /**
     * @param QueryGeneratorInterface $generator
     */
    public function addGenerator(QueryGeneratorInterface $generator);

    /**
     * @param string $adapterName
     * @param string $objectType
     * @param string $queryType
     * @param string $identifier
     *
     * @return QueryInterface
     */
    public function create($adapterName, $objectType, $queryType, $identifier = null);
}
