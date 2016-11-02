<?php

namespace PlentyConnector\Connector\ServiceBus;

use League\Tactician\Middleware;

/**
 * Class ServiceBusFactory
 *
 * @package PlentyConnector\Connector\CommandBus
 */
class ServiceBusFactory
{
    /**
     * @param Middleware[] $middlewares
     *
     * @return ServiceBus
     */
    public function factory(Middleware ...$middlewares)
    {
        return new ServiceBus($middlewares);
    }
}
