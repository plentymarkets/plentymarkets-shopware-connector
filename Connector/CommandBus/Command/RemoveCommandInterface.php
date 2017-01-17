<?php

namespace PlentyConnector\Connector\CommandBus\Command;

use PlentyConnector\Connector\TransferObject\TransferObjectInterface;

/**
 * Class RemoveCommandInterfaca
 */
interface RemoveCommandInterfaca extends CommandInterface
{
    /**
     * @return string
     */
    public function getAdapterName();

    /**
     * @return string
     */
    public function getObjectIdentifier();
}
