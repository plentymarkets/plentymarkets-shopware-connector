<?php

namespace SystemConnector\BacklogService;

use SystemConnector\BacklogService\Command\HandleBacklogElementCommand;
use SystemConnector\ServiceBus\Command\CommandInterface;

interface BacklogServiceInterface
{
    const STATUS_OPEN = 'open';
    const STATUS_PROCESSED = 'processed';

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
