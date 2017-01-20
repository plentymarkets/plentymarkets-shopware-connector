<?php

namespace PlentyConnector\Connector\ServiceBus\CommandFactory;

use PlentyConnector\Connector\ServiceBus\Command\CommandInterface;
use PlentyConnector\Connector\ServiceBus\CommandFactory\Exception\MissingCommandException;
use PlentyConnector\Connector\ServiceBus\CommandFactory\Exception\MissingCommandGeneratorException;
use PlentyConnector\Connector\ServiceBus\CommandGenerator\CommandGeneratorInterface;

/**
 * Class CommandFactoryInterface.
 */
interface CommandFactoryInterface
{
    /**
     * @param CommandGeneratorInterface $generator
     */
    public function addGenerator(CommandGeneratorInterface $generator);

    /**
     * @param string $adapterName
     * @param string $objectType
     * @param string $commandType
     * @param mixed $payload
     *
     * @return CommandInterface
     *
     * @throws MissingCommandGeneratorException
     * @throws MissingCommandException
     */
    public function create($adapterName, $objectType, $commandType, $payload = null);
}
