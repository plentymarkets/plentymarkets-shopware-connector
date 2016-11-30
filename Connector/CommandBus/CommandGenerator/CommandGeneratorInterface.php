<?php

namespace PlentyConnector\Connector\CommandBus\CommandGenerator;

use PlentyConnector\Connector\CommandBus\Command\CommandInterface;
use PlentyConnector\Connector\TransferObject\TransferObjectInterface;

/**
 * Interface CommandGeneratorInterface
 */
interface CommandGeneratorInterface
{
    /**
     * @param string $transferObjectType
     *
     * @return boolean
     */
    public function supports($transferObjectType);

    /**
     * @param TransferObjectInterface $transferObject
     * @param string $adapterName
     *
     * @return CommandInterface
     */
    public function generateHandleCommand(TransferObjectInterface $transferObject, $adapterName);
}
