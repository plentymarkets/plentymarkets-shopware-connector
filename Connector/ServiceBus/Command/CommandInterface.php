<?php

namespace PlentyConnector\Connector\ServiceBus\Command;

interface CommandInterface
{
    /**
     * Array representation of the command for debug purpose.
     *
     * @return array
     */
    public function toArray();

    /**
     * Priority of the command. Higher priority means earlier processing
     * when the command is retrieved from the backlog.
     *
     * @return int
     */
    public function getPriority();
}
