<?php

namespace PlentyConnector\Connector\ServiceBus;

use PlentyConnector\Connector\QueryBus\Response\ResponseCollection;
use PlentyConnector\Connector\QueryBus\Response\ResponseItem;

/**
 * Interface ServiceBusInterface
 *
 * @package PlentyConnector\Connector\ServiceBus
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
