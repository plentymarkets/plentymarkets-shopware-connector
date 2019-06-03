<?php

namespace SystemConnector\ServiceBus\QueryFactory;

use SystemConnector\ServiceBus\Query\QueryInterface;

interface QueryFactoryInterface
{
    /**
     * @param string $adapterName
     * @param string $objectType
     * @param string $queryType
     * @param mixed  $payload
     *
     * @return QueryInterface
     */
    public function create($adapterName, $objectType, $queryType, $payload = null): QueryInterface;
}
