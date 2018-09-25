<?php

namespace PlentyConnector\Connector\BacklogService\Command;

use PlentyConnector\Connector\ServiceBus\Command\CommandInterface;

class HandleBacklogElementCommand implements CommandInterface
{
    /**
     * @var CommandInterface
     */
    private $command;

    public function __construct(CommandInterface $command)
    {
        $this->command = $command;
    }

    /**
     * {@inheritdoc}
     */
    public function toArray()
    {
        return [
            'command' => $this->command,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getPriority()
    {
        return 0;
    }

    /**
     * {@inheritdoc}
     */
    public function getPayload()
    {
        return $this->command;
    }
}
