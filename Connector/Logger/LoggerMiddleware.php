<?php

namespace SystemConnector\Logger;

use Exception;
use League\Tactician\Middleware;

class LoggerMiddleware implements Middleware
{
    /**
     * @var ClassNameFormatter
     */
    private $formatter;

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

            $this->formatter->logCommandProcessed($command, $returnValue);

            return $returnValue;
        } catch (Exception $exception) {
            $this->formatter->logCommandFailed($command, $exception);
        }

        return $returnValue;
    }
}
