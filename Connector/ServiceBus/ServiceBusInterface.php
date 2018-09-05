<?php

namespace PlentyConnector\Connector\ServiceBus;

interface ServiceBusInterface
{
    /**
     * @param mixed $object
     *
     * @return mixed
     */
    public function handle($object);
}
