<?php

namespace PlentyConnector\Connector\Logger;

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
     * {@inheritDoc}
     */
    public function logCommandReceived(LoggerInterface $logger, $command)
    {
        $message = $this->getRecievedMessage($command);
        $payload = $this->getPayload($command);

        $logger->log($this->commandReceivedLevel, $message, $payload);
    }

    /**
     * @param $command
     *
     * @return string
     */
    private function getRecievedMessage($command)
    {
        return $this->getType($command) . ' received: ' . $this->getClassName($command);
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
     * @param $command
     *
     * @return string
     */
    protected function getClassName($command)
    {
        return substr(strrchr(get_class($command), '\\'), 1);
    }

    /**
     * @param $command
     *
     * @return array
     */
    protected function getPayload($command)
    {
        if (!($command instanceof CommandInterface)
            && !($command instanceof QueryInterface)
            && !($command instanceof EventInterface)
        ) {
            return [];
        }

        return $command->getPayload();
    }

    /**
     * {@inheritDoc}
     */
    public function logCommandSucceeded(LoggerInterface $logger, $command, $returnValue)
    {
        $message = $this->getSucceededMessage($command);
        $payload = $this->getPayload($command);

        $logger->log($this->commandSucceededLevel, $message, $payload);
    }

    /**
     * @param $command
     *
     * @return string
     */
    private function getSucceededMessage($command)
    {
        return $this->getType($command) . ' succeeded: ' . $this->getClassName($command);
    }

    /**
     * {@inheritDoc}
     */
    public function logCommandFailed(LoggerInterface $logger, $command, Exception $exception)
    {
        $message = $this->getFailedMessage($command);
        $payload = $this->getPayload($command);

        $payload = array_merge($payload, ['exception' => $exception]);

        $logger->log(
            $this->commandFailedLevel,
            $message,
            $payload
        );
    }

    /**
     * @param $command
     *
     * @return string
     */
    private function getFailedMessage($command)
    {
        return $this->getType($command) . ' failed: ' . $this->getClassName($command);
    }
}
