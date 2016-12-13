<?php

namespace PlentyConnector\Logger;

use Exception;
use League\Tactician\Logger\Formatter\Formatter;
use PlentyConnector\Connector\CommandBus\Command\CommandInterface;
use PlentyConnector\Connector\EventBus\Event\EventInterface;
use PlentyConnector\Connector\QueryBus\Query\QueryInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

/**
 * Returns log messages only dump the Command & Exception's class names.
 */
class ClassNameFormatter implements Formatter
{
    /**
     * @var string
     */
    private $commandReceivedLevel;

    /**
     * @var string
     */
    private $commandSucceededLevel;

    /**
     * @var string
     */
    private $commandFailedLevel;

    /**
     * @param string $commandReceivedLevel
     * @param string $commandSucceededLevel
     * @param string $commandFailedLevel
     */
    public function __construct(
        $commandReceivedLevel = LogLevel::DEBUG,
        $commandSucceededLevel = LogLevel::DEBUG,
        $commandFailedLevel = LogLevel::ERROR
    ) {
        $this->commandReceivedLevel = $commandReceivedLevel;
        $this->commandSucceededLevel = $commandSucceededLevel;
        $this->commandFailedLevel = $commandFailedLevel;
    }

    /**
     * @param $command
     *
     * @return string
     */
    protected function getType($command)
    {
        if ($command instanceof CommandInterface) {
            return 'Command';
        }

        if ($command instanceof QueryInterface) {
            return 'Query';
        }

        if ($command instanceof EventInterface) {
            return 'Query';
        }

        return 'Undefined';
    }

    /**
     * {@inheritDoc}
     */
    public function logCommandReceived(LoggerInterface $logger, $command)
    {
        $type = $this->getType($command);

        $logger->log($this->commandReceivedLevel, $type . ' received: ' . get_class($command), []);
    }

    /**
     * {@inheritDoc}
     */
    public function logCommandSucceeded(LoggerInterface $logger, $command, $returnValue)
    {
        $type = $this->getType($command);

        $logger->log($this->commandSucceededLevel, $type . ' succeeded: ' . get_class($command), []);
    }

    /**
     * {@inheritDoc}
     */
    public function logCommandFailed(LoggerInterface $logger, $command, Exception $e)
    {
        $type = $this->getType($command);

        $logger->log(
            $this->commandFailedLevel,
            $type . ' failed: ' . get_class($command),
            ['exception' => $e]
        );
    }
}
