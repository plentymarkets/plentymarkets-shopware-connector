<?php

namespace PlentyConnector\Connector\CommandBus\Command;

/**
 * Class CommandInterface
 *
 * @package PlentyConnector\Connector\Connector\Command
 */
interface CommandInterface
{
    /**
     * @return array
     */
    public function getPayload();

    /**
     * @param array $payload
     */
    public function setPayload(array $payload);
}
