<?php

namespace SystemConnector\ServiceBus;

use League\Tactician\Middleware;

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
