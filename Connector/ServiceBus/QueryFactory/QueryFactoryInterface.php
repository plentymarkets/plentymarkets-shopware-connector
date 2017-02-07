<?php

namespace PlentyConnector\Connector\ServiceBus\QueryFactory;

use PlentyConnector\Connector\ServiceBus\Query\QueryInterface;
use PlentyConnector\Connector\ServiceBus\QueryFactory\Exception\MissingQueryException;
use PlentyConnector\Connector\ServiceBus\QueryFactory\Exception\MissingQueryGeneratorException;
use PlentyConnector\Connector\ServiceBus\QueryGenerator\QueryGeneratorInterface;

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
     * @param mixed  $payload
     *
     * @throws MissingQueryGeneratorException
     * @throws MissingQueryException
     *
     * @return QueryInterface
     */
    public function create($adapterName, $objectType, $queryType, $payload = null);
}
