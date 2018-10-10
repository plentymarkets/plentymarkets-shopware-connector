<?php

namespace SystemConnector\ServiceBus\CommandFactory;

use SystemConnector\ServiceBus\Command\CommandInterface;

interface CommandFactoryInterface
{
    /**
     * @param string $adapterName
     * @param string $objectType
     * @param string $commandType
     * @param int    $priority
     * @param mixed  $payload
     *
     * @return CommandInterface
     */
    public function create($adapterName, $objectType, $commandType, $priority, $payload);
}
