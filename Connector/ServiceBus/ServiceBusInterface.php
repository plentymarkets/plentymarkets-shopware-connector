<?php

namespace SystemConnector\ServiceBus;

interface ServiceBusInterface
{
    /**
     * @param mixed $object
     *
     * @return mixed
     */
    public function handle($object);
}
