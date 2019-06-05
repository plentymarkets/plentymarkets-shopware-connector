<?php

namespace SystemConnector\BacklogService\Command;

use SystemConnector\ServiceBus\Command\CommandInterface;

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
    public function toArray(): array
    {
        return [
            'command' => $this->command,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getPriority(): int
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
