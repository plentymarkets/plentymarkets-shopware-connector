<?php

namespace PlentyConnector\Connector\BacklogService;

use PlentyConnector\Connector\ServiceBus\Command\CommandInterface;

/**
 * Interface BacklogServiceInterface
 */
interface BacklogServiceInterface
{
    /**
     * @param CommandInterface $command
     */
    public function enqueue(CommandInterface $command);

    /**
     * @return null|CommandInterface
     */
    public function dequeue();

    /**
     * Returns an array of informations about the queue
     *
     * @return array
     */
    public function getInfo();
}
