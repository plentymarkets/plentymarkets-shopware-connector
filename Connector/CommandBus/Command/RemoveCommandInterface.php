<?php

namespace PlentyConnector\Connector\CommandBus\Command;

/**
 * Class RemoveCommandInterface
 */
interface RemoveCommandInterface extends CommandInterface
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
