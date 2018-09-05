<?php

namespace PlentyConnector\Connector\ServiceBus\Command;

interface CommandInterface
{
    /**
     * @return array
     */
    public function toArray();
}
