<?php

namespace SystemConnector\ServiceBus\Command;

interface CommandInterface
{
    /**
     * Priority of the command. Higher priority means earlier processing
     * when the command is retrieved from the backlog.
     *
     * @return int
     */
    public function getPriority(): int;

    /**
     * Actual payload of the command. Possibly a TransferObject, UUID or even a CommandInterface.
     *
     * @return mixed
     */
    public function getPayload();

    /**
     * Array representation of the command for debug purpose.
     *
     * @return array
     */
    public function toArray(): array;
}
