<?php

namespace PlentyConnector\Connector\CommandBus\CommandFactory;

use Assert\Assertion;
use PlentyConnector\Connector\CommandBus\CommandGenerator\CommandGeneratorInterface;
use PlentyConnector\Connector\CommandBus\CommandType;
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
    public function create(TransferObjectInterface $object, $adapterName, $commandType)
    {
        Assertion::string($adapterName);
        Assertion::string($commandType);
        Assertion::inArray($commandType, CommandType::getAllTypes());

        /**
         * @var CommandGeneratorInterface[] $generators
         */
        $generators = array_filter($this->generators,
            function (CommandGeneratorInterface $generator) use ($object) {
                return $generator->supports($object->getType());
            }
        );

        $generator = array_shift($generators);

        if (null === $generator) {
            return null;
        }

        switch ($commandType) {
            case CommandType::HANDLE:
                return $generator->generateHandleCommand($object, $adapterName);
            case CommandType::REMOVE:
                return $generator->generateRemoveCommand($object, $adapterName);
        }
    }
}
