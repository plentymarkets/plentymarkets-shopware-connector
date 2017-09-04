<?php

namespace PlentyConnector\Connector\ServiceBus\CommandGenerator\Stock;

use PlentyConnector\Connector\ServiceBus\Command\Stock\HandleStockCommand;
use PlentyConnector\Connector\ServiceBus\Command\Stock\RemoveStockCommand;
use PlentyConnector\Connector\ServiceBus\CommandGenerator\CommandGeneratorInterface;
use PlentyConnector\Connector\TransferObject\Product\Stock\Stock;
use PlentyConnector\Connector\TransferObject\TransferObjectInterface;

/**
 * Class StockCommandGenerator
 */
class StockCommandGenerator implements CommandGeneratorInterface
{
    /**
     * {@inheritdoc}
     */
    public function supports($transferObjectType)
    {
        return $transferObjectType === Stock::TYPE;
    }

    /**
     * {@inheritdoc}
     */
    public function generateHandleCommand($adapterName, TransferObjectInterface $transferObject)
    {
        return new HandleStockCommand($adapterName, $transferObject);
    }

    /**
     * {@inheritdoc}
     */
    public function generateRemoveCommand($adapterName, $objectIdentifier)
    {
        return new RemoveStockCommand($adapterName, $objectIdentifier);
    }
}
