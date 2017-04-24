<?php

namespace PlentyConnector\Connector\Logger;

use Exception;
use League\Tactician\Middleware;

/**
 * Class LoggerMiddleware
 */
class LoggerMiddleware implements Middleware
{
    /**
     * @var ClassNameFormatter
     */
    private $formatter;

    /**
     * @param ClassNameFormatter $formatter
     */
    public function __construct(ClassNameFormatter $formatter)
    {
        $this->formatter = $formatter;
    }

    /**
     * {@inheritdoc}
     */
    public function execute($command, callable $next)
    {
        $this->formatter->logCommandReceived($command);

        $returnValue = false;

        try {
            $returnValue = $next($command);

            $this->formatter->logCommandSucceeded($command, $returnValue);

            return $returnValue;
        } catch (Exception $e) {
            $this->formatter->logCommandFailed($command, $e);
        }

        return $returnValue;
    }
}
