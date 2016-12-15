<?php

namespace PlentyConnector\Connector\CommandBus\CommandFactory;

use PlentyConnector\Connector\CommandBus\CommandGenerator\CommandGeneratorInterface;
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
    public function create(TransferObjectInterface $object, $adapterName)
    {
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

        return $generator->generateHandleCommand($object, $adapterName);
    }
}
