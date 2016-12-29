<?php

namespace PlentyConnector\Connector\CommandBus\CommandGenerator\Manufacturer;

use PlentyConnector\Connector\CommandBus\Command\Manufacturer\HandleManufacturerCommand;
use PlentyConnector\Connector\CommandBus\Command\Manufacturer\RemoveManufacturerCommand;
use PlentyConnector\Connector\CommandBus\CommandGenerator\CommandGeneratorInterface;
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
        return $transferObjectType === Manufacturer::getType();
    }

    /**
     * {@inheritdoc}
     */
    public function generateHandleCommand(TransferObjectInterface $transferObject, $adapterName)
    {
        return new HandleManufacturerCommand($transferObject, $adapterName);
    }

    /**
     * {@inheritdoc}
     */
    public function generateRemoveCommand(TransferObjectInterface $transferObject, $adapterName)
    {
        return new RemoveManufacturerCommand($transferObject, $adapterName);
    }
}
