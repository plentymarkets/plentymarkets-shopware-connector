<?php

namespace PlentyConnector\Connector\ServiceBus\CommandGenerator\Variation;

use PlentyConnector\Connector\ServiceBus\Command\Variation\HandleVariationCommand;
use PlentyConnector\Connector\ServiceBus\Command\Variation\RemoveVariationCommand;
use PlentyConnector\Connector\ServiceBus\CommandGenerator\CommandGeneratorInterface;
use PlentyConnector\Connector\TransferObject\Product\Variation\Variation;
use PlentyConnector\Connector\TransferObject\TransferObjectInterface;

/**
 * Class VariationCommandGenerator
 */
class VariationCommandGenerator implements CommandGeneratorInterface
{
    /**
     * {@inheritdoc}
     */
    public function supports($transferObjectType)
    {
        return $transferObjectType === Variation::TYPE;
    }

    /**
     * {@inheritdoc}
     */
    public function generateHandleCommand($adapterName, TransferObjectInterface $transferObject)
    {
        return new HandleVariationCommand($adapterName, $transferObject);
    }

    /**
     * {@inheritdoc}
     */
    public function generateRemoveCommand($adapterName, $objectIdentifier)
    {
        return new RemoveVariationCommand($adapterName, $objectIdentifier);
    }
}
