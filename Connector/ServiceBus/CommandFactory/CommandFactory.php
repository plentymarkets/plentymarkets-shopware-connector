<?php

namespace PlentyConnector\Connector\ServiceBus\CommandFactory;

use Assert\Assertion;
use PlentyConnector\Connector\ServiceBus\CommandFactory\Exception\MissingCommandException;
use PlentyConnector\Connector\ServiceBus\CommandFactory\Exception\MissingCommandGeneratorException;
use PlentyConnector\Connector\ServiceBus\CommandGenerator\CommandGeneratorInterface;
use PlentyConnector\Connector\ServiceBus\CommandType;
use PlentyConnector\Connector\TransferObject\TransferObjectInterface;

/**
 * Class CommandFactoryInterface.
 */
class CommandFactory implements CommandFactoryInterface
{
    /**
     * @var CommandGeneratorInterface[]
     */
    private $generators = [];

    /**
     * {@inheritdoc}
     */
    public function addGenerator(CommandGeneratorInterface $generator)
    {
        $this->generators[] = $generator;
    }

    /**
     * {@inheritdoc}
     */
    public function create($adapterName, $objectType, $commandType, $payload = null)
    {
        Assertion::string($adapterName);
        Assertion::string($objectType);
        Assertion::inArray($commandType, CommandType::getAllTypes());

        if ($commandType === CommandType::HANDLE) {
            Assertion::isInstanceOf($payload, TransferObjectInterface::class);
        }

        if ($commandType === CommandType::REMOVE) {
            Assertion::uuid($payload);
        }

        /**
         * @var CommandGeneratorInterface[] $generators
         */
        $generators = array_filter($this->generators, function (CommandGeneratorInterface $generator) use ($objectType) {
            return $generator->supports($objectType);
        });

        $generator = array_shift($generators);

        if (null === $generator) {
            throw MissingCommandGeneratorException::fromObjectData($objectType, $commandType);
        }

        $command = null;

        switch ($commandType) {
            case CommandType::HANDLE:
                $command = $generator->generateHandleCommand($adapterName, $payload);
                break;
            case CommandType::REMOVE:
                $command = $generator->generateRemoveCommand($adapterName, $payload);
                break;
        }

        if (null === $command) {
            throw MissingCommandException::fromObjectData($objectType, $commandType);
        }

        return $command;
    }
}
