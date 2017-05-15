<?php

namespace PlentyConnector\Components\Bundle\CommandGenerator;

use PlentyConnector\Components\Bundle\Command\HandleBundleCommand;
use PlentyConnector\Components\Bundle\Command\RemoveBundleCommand;
use PlentyConnector\Components\Bundle\TransferObject\Bundle;
use PlentyConnector\Connector\ServiceBus\CommandGenerator\CommandGeneratorInterface;
use PlentyConnector\Connector\TransferObject\TransferObjectInterface;

/**
 * Class BundleCommandGenerator
 */
class BundleCommandGenerator implements CommandGeneratorInterface
{
    /**
     * {@inheritdoc}
     */
    public function supports($transferObjectType)
    {
        return $transferObjectType === Bundle::TYPE;
    }

    /**
     * {@inheritdoc}
     */
    public function generateHandleCommand($adapterName, TransferObjectInterface $transferObject)
    {
        return new HandleBundleCommand($adapterName, $transferObject);
    }

    /**
     * {@inheritdoc}
     */
    public function generateRemoveCommand($adapterName, $objectIdentifier)
    {
        return new RemoveBundleCommand($adapterName, $objectIdentifier);
    }
}
