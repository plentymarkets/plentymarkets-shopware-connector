<?php

namespace PlentyConnector\Connector\ServiceBus\QueryFactory;

use PlentyConnector\Connector\ServiceBus\Query\QueryInterface;
use PlentyConnector\Connector\ServiceBus\QueryFactory\Exception\MissingQueryException;

/**
 * Class QueryFactoryInterface.
 */
interface QueryFactoryInterface
{
    /**
     * @param string $adapterName
     * @param string $objectType
     * @param string $queryType
     * @param mixed  $payload
     *
     * @throws MissingQueryException
     *
     * @return QueryInterface
     */
    public function create($adapterName, $objectType, $queryType, $payload = null);
}
