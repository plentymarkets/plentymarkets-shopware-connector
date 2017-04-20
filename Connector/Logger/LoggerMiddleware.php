<?php

namespace PlentyConnector\Connector\Logger;


use League\Tactician\Logger\Formatter\Formatter;
use League\Tactician\Middleware;
use Psr\Log\LoggerInterface;
use Exception;

/**
 * Class LoggerMiddleware
 */
class LoggerMiddleware implements Middleware
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Formatter
     */
    private $formatter;

    /**
     * @param Formatter $formatter
     * @param LoggerInterface $logger
     */
    public function __construct(Formatter $formatter, LoggerInterface $logger)
    {
        $this->formatter = $formatter;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function execute($command, callable $next)
    {
        $this->formatter->logCommandReceived($this->logger, $command);

        try {
            $returnValue = $next($command);

            $this->formatter->logCommandSucceeded($this->logger, $command, $returnValue);

            return $returnValue;
        } catch (Exception $e) {
            $this->formatter->logCommandFailed($this->logger, $command, $e);
        }
    }
}
