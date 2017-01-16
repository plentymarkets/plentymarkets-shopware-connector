<?php

namespace PlentyConnector\Connector\CommandBus\CommandFactory;

use PlentyConnector\Connector\CommandBus\Command\CommandInterface;
use PlentyConnector\Connector\CommandBus\CommandFactory\Exception\MissingCommandException;
use PlentyConnector\Connector\CommandBus\CommandFactory\Exception\MissingCommandGeneratorException;
use PlentyConnector\Connector\CommandBus\CommandGenerator\CommandGeneratorInterface;

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
