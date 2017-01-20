<?php

namespace PlentyConnector\Connector\ServiceBus\Command;

use PlentyConnector\Connector\TransferObject\TransferObjectInterface;

/**
 * Interface HandleCommandInterface
 */
interface HandleCommandInterface extends CommandInterface
{
    /**
     * @return string
     */
    public function getAdapterName();

    /**
     * @return TransferObjectInterface
     */
    public function getTransferObject();
}
