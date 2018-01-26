<?php

namespace PlentyConnector\Connector\ServiceBus;

/**
 * Interface ServiceBusInterface.
 */
interface ServiceBusInterface
{
    /**
     * @param mixed $object
     *
     * @return mixed
     */
    public function handle($object);
}
