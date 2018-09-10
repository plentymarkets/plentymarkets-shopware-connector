<?php

namespace PlentyConnector\Connector\ServiceBus\CommandFactory;

use Assert\Assertion;
use PlentyConnector\Connector\ServiceBus\Command\TransferObjectCommand;
use PlentyConnector\Connector\ServiceBus\CommandFactory\Exception\MissingCommandException;
use PlentyConnector\Connector\ServiceBus\CommandType;
use PlentyConnector\Connector\TransferObject\TransferObjectInterface;

class CommandFactory implements CommandFactoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function create($adapterName, $objectType, $commandType, $priority, $payload)
    {
        Assertion::string($adapterName);
        Assertion::string($objectType);
        Assertion::inArray($commandType, CommandType::getAllTypes());
        Assertion::integer($priority);

        if ($commandType === CommandType::HANDLE) {
            Assertion::isInstanceOf($payload, TransferObjectInterface::class);
        }

        if ($commandType === CommandType::REMOVE) {
            Assertion::uuid($payload);
        }

        $command = null;

        switch ($commandType) {
            case CommandType::HANDLE:
                $command = new TransferObjectCommand($adapterName, $objectType, $commandType, $priority, $payload);

                break;
            case CommandType::REMOVE:
                $command = new TransferObjectCommand($adapterName, $objectType, $commandType, $priority, $payload);

                break;
        }

        if (null === $command) {
            throw MissingCommandException::fromObjectData($objectType, $commandType);
        }

        return $command;
    }
}
