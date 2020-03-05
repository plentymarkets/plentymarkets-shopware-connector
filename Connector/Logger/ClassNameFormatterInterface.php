<?php

namespace SystemConnector\Logger;

use Exception;

interface ClassNameFormatterInterface
{
    /**
     * @param mixed $command
     */
    public function logCommandReceived($command);

    /**
     * @param mixed $command
     * @param mixed $returnValue
     */
    public function logCommandProcessed($command, $returnValue);

    /**
     * @param mixed $command
     */
    public function logCommandFailed($command, Exception $e);
}
