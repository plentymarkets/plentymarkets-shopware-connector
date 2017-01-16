<?php

namespace PlentyConnector\Connector\QueryBus\QueryFactory;

use PlentyConnector\Connector\QueryBus\Query\QueryInterface;
use PlentyConnector\Connector\QueryBus\QueryFactory\Exception\MissingQueryException;
use PlentyConnector\Connector\QueryBus\QueryFactory\Exception\MissingQueryGeneratorException;
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
     * @param mixed $payload
     *
     * @return QueryInterface
     *
     * @throws MissingQueryGeneratorException
     * @throws MissingQueryException
     */
    public function create($adapterName, $objectType, $queryType, $payload = null);
}
