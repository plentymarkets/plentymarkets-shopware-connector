<?php

namespace PlentyConnector\Connector\ServiceBus\CommandFactory;

use PlentyConnector\Connector\ServiceBus\Command\CommandInterface;
use PlentyConnector\Connector\ServiceBus\CommandFactory\Exception\MissingCommandException;

/**
 * Class CommandFactoryInterface.
 */
interface CommandFactoryInterface
{
    /**
     * @param string $adapterName
     * @param string $objectType
     * @param string $commandType
     * @param mixed  $payload
     *
     * @throws MissingCommandException
     *
     * @return CommandInterface
     */
    public function create($adapterName, $objectType, $commandType, $payload = null);
}
