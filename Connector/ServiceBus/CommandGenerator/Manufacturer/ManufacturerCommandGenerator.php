<?php

namespace PlentyConnector\Connector\ServiceBus\CommandGenerator\Manufacturer;

use PlentyConnector\Connector\ServiceBus\Command\Manufacturer\HandleManufacturerCommand;
use PlentyConnector\Connector\ServiceBus\Command\Manufacturer\RemoveManufacturerCommand;
use PlentyConnector\Connector\ServiceBus\CommandGenerator\CommandGeneratorInterface;
use PlentyConnector\Connector\TransferObject\Manufacturer\Manufacturer;
use PlentyConnector\Connector\TransferObject\TransferObjectInterface;

/**
 * Class ManufacturerCommandGenerator
 */
class ManufacturerCommandGenerator implements CommandGeneratorInterface
{
    /**
     * {@inheritdoc}
     */
    public function supports($transferObjectType)
    {
        return $transferObjectType === Manufacturer::TYPE;
    }

    /**
     * {@inheritdoc}
     */
    public function generateHandleCommand($adapterName, TransferObjectInterface $transferObject)
    {
        return new HandleManufacturerCommand($adapterName, $transferObject);
    }

    /**
     * {@inheritdoc}
     */
    public function generateRemoveCommand($adapterName, $objectIdentifier)
    {
        return new RemoveManufacturerCommand($adapterName, $objectIdentifier);
    }
}
