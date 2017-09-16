<?php

namespace PlentyConnector\Connector\ServiceBus\Command;

/**
 * Class CommandInterface.
 */
interface CommandInterface
{
    /**
     * @return array
     */
    public function toArray();
}
