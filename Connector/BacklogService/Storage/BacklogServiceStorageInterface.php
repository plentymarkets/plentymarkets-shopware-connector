<?php

namespace SystemConnector\BacklogService\Storage;

use SystemConnector\BacklogService\Command\HandleBacklogElementCommand;
use SystemConnector\ServiceBus\Command\CommandInterface;

interface BacklogServiceStorageInterface
{
    /**
     * enqueues a command to the backlog
     */
    public function enqueue(CommandInterface $command);

    /**
     * dequeue the next possible command
     *
     * @return null|HandleBacklogElementCommand
     */
    public function dequeue();

    /**
     * Returns an array of informations about the queue
     */
    public function getInfo(): array;
}
