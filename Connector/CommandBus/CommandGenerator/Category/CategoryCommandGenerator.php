<?php

namespace PlentyConnector\Connector\CommandBus\CommandGenerator\Category;

use PlentyConnector\Connector\CommandBus\Command\Category\HandleCategoryCommand;
use PlentyConnector\Connector\CommandBus\Command\Category\RemoveCategoryCommand;
use PlentyConnector\Connector\CommandBus\CommandGenerator\CommandGeneratorInterface;
use PlentyConnector\Connector\TransferObject\Category\Category;
use PlentyConnector\Connector\TransferObject\TransferObjectInterface;

/**
 * Class CategoryCommandGenerator
 */
class CategoryCommandGenerator implements CommandGeneratorInterface
{
    /**
     * {@inheritdoc}
     */
    public function supports($transferObjectType)
    {
        return $transferObjectType === Category::TYPE;
    }

    /**
     * {@inheritdoc}
     */
    public function generateHandleCommand($adapterName, TransferObjectInterface $transferObject)
    {
        return new HandleCategoryCommand($adapterName, $transferObject);
    }

    /**
     * {@inheritdoc}
     */
    public function generateRemoveCommand($adapterName, $objectIdentifier)
    {
        return new RemoveCategoryCommand($adapterName, $objectIdentifier);
    }
}
