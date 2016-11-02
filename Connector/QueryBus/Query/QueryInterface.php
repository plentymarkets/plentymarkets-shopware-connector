<?php

namespace PlentyConnector\Connector\QueryBus\Query;

/**
 * Class QueryInterface
 *
 * @package PlentyConnector\Connector\Connector\Query
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
