<?php

namespace PlentyConnector\Connector\CommandBus\CommandFactory;

use PlentyConnector\Connector\CommandBus\CommandGenerator\CommandGeneratorInterface;
use PlentyConnector\Connector\TransferObject\Definition\DefinitionInterface;
use PlentyConnector\Connector\TransferObject\TransferObjectInterface;

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
     * @param TransferObjectInterface $object
     * @param string $adapterName
     *
     * @return CommandInterface
     */
    public function create(TransferObjectInterface $object, $adapterName);
}
