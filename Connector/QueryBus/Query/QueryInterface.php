<?php

namespace PlentyConnector\Connector\QueryBus\Query;

/**
 * Class QueryInterface.
 */
interface QueryInterface
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
