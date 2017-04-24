<?php

namespace PlentyConnector\Connector\Logger;

use Exception;

/**
 * Interface ClassNameFormatterInterface
 */
interface ClassNameFormatterInterface
{
    /**
     * @param object $command
     */
    public function logCommandReceived($command);

    /**
     * @param object $command
     * @param mixed  $returnValue
     */
    public function logCommandSucceeded($command, $returnValue);

    /**
     * @param object    $command
     * @param Exception $e
     */
    public function logCommandFailed($command, Exception $e);
}
