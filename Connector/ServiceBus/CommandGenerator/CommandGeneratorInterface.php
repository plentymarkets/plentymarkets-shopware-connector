<?php

namespace PlentyConnector\Connector\ServiceBus\CommandGenerator;

use PlentyConnector\Connector\ServiceBus\Command\CommandInterface;
use PlentyConnector\Connector\TransferObject\TransferObjectInterface;

/**
 * Interface CommandGeneratorInterface
 */
interface CommandGeneratorInterface
{
    /**
     * @param string $transferObjectType
     *
     * @return bool
     */
    public function supports($transferObjectType);

    /**
     * @param string                  $adapterName
     * @param TransferObjectInterface $transferObject
     *
     * @return CommandInterface
     */
    public function generateHandleCommand($adapterName, TransferObjectInterface $transferObject);

    /**
     * @param string $adapterName
     * @param string $objectIdentifier
     *
     * @return CommandInterface
     */
    public function generateRemoveCommand($adapterName, $objectIdentifier);
}
