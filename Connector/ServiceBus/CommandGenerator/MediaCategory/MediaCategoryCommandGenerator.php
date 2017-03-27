<?php

namespace PlentyConnector\Connector\ServiceBus\CommandGenerator\MediaCategory;

use PlentyConnector\Connector\ServiceBus\Command\MediaCategory\HandleMediaCategoryCommand;
use PlentyConnector\Connector\ServiceBus\Command\MediaCategory\RemoveMediaCategoryCommand;
use PlentyConnector\Connector\ServiceBus\CommandGenerator\CommandGeneratorInterface;
use PlentyConnector\Connector\TransferObject\MediaCategory\MediaCategory;
use PlentyConnector\Connector\TransferObject\TransferObjectInterface;

/**
 * Class MediaCategoryCommandGenerator.
 */
class MediaCategoryCommandGenerator implements CommandGeneratorInterface
{
    /**
     * {@inheritdoc}
     */
    public function supports($transferObjectType)
    {
        return $transferObjectType === MediaCategory::TYPE;
    }

    /**
     * {@inheritdoc}
     */
    public function generateHandleCommand($adapterName, TransferObjectInterface $transferObject)
    {
        return new HandleMediaCategoryCommand($adapterName, $transferObject);
    }

    /**
     * {@inheritdoc}
     */
    public function generateRemoveCommand($adapterName, $objectIdentifier)
    {
        return new RemoveMediaCategoryCommand($adapterName, $objectIdentifier);
    }
}
