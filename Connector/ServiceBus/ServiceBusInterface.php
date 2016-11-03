<?php

namespace PlentyConnector\Connector\ServiceBus;

/**
 * Interface ServiceBusInterface.
 */
interface ServiceBusInterface
{
    /**
     * @param $object
     *
     * @return object
     */
    public function handle($object);
}
