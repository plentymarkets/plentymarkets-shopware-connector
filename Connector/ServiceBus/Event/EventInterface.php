<?php

namespace PlentyConnector\Connector\ServiceBus\Event;

/**
 * Class EventInterface.
 */
interface EventInterface
{
    /**
     * @return array
     */
    public function getPayload();

    /**
     * @param array $payload
     */
    public function setPayload(array $payload = []);
}
