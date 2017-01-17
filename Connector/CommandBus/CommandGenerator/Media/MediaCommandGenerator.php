<?php

namespace PlentyConnector\Connector\CommandBus\CommandGenerator\Media;

use PlentyConnector\Connector\CommandBus\Command\Media\HandleMediaCommand;
use PlentyConnector\Connector\CommandBus\Command\Media\RemoveMediaCommand;
use PlentyConnector\Connector\CommandBus\CommandGenerator\CommandGeneratorInterface;
use PlentyConnector\Connector\TransferObject\Media\Media;
use PlentyConnector\Connector\TransferObject\TransferObjectInterface;

/**
 * Class MediaCommandGenerator
 */
class MediaCommandGenerator implements CommandGeneratorInterface
{
    /**
     * {@inheritdoc}
     */
    public function supports($transferObjectType)
    {
        return $transferObjectType === Media::TYPE;
    }

    /**
     * {@inheritdoc}
     */
    public function generateHandleCommand($adapterName, TransferObjectInterface $transferObject)
    {
        return new HandleMediaCommand($adapterName, $transferObject);
    }

    /**
     * {@inheritdoc}
     */
    public function generateRemoveCommand($adapterName, $objectIdentifier)
    {
        return new RemoveMediaCommand($adapterName, $objectIdentifier);
    }
}
