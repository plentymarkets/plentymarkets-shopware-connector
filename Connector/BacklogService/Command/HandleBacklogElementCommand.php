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
     * @return CommandInterface
     */
    public function getCommand()
    {
        return $this->command;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'command' => $this->command,
        ];
    }
}
