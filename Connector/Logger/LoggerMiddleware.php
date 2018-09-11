<?php

namespace PlentyConnector\Connector\Logger;

use Exception;
use League\Tactician\Middleware;

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
        } catch (Exception $exception) {
            $this->formatter->logCommandFailed($command, $exception);
        }

        return $returnValue;
    }
}
