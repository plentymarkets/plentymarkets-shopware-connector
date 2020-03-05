<?php

namespace SystemConnector\ServiceBus;

use League\Tactician\Middleware;

class ServiceBusFactory
{
    /**
     * @param Middleware[] $middlewares
     */
    public function factory(Middleware ...$middlewares): ServiceBus
    {
        return new ServiceBus($middlewares);
    }
}
