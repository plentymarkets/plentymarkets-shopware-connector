<?php

namespace PlentyConnector\Connector\Logger;

use Exception;
use PlentyConnector\Connector\ServiceBus\Command\CommandInterface;
use PlentyConnector\Connector\ServiceBus\Event\EventInterface;
use PlentyConnector\Connector\ServiceBus\Query\QueryInterface;
use PlentyConnector\Connector\TransferObject\TransferObjectInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

/**
 * Returns log messages only dump the Command & Exception's class names.
 */
class ClassNameFormatter implements ClassNameFormatterInterface
{
    /**
     * @var LoggerInterface
     */
    private $logger;

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
     * @param LoggerInterface $logger
     * @param string          $commandReceivedLevel
     * @param string          $commandSucceededLevel
     * @param string          $commandFailedLevel
     */
    public function __construct(
        LoggerInterface $logger,
        $commandReceivedLevel = LogLevel::DEBUG,
        $commandSucceededLevel = LogLevel::DEBUG,
        $commandFailedLevel = LogLevel::ERROR
    ) {
        $this->logger = $logger;
        $this->commandReceivedLevel = $commandReceivedLevel;
        $this->commandSucceededLevel = $commandSucceededLevel;
        $this->commandFailedLevel = $commandFailedLevel;
    }

    /**
     * {@inheritdoc}
     */
    public function logCommandReceived($command)
    {
        $message = $this->getRecievedMessage($command);
        $payload = $this->getPayload($command);

        $this->logger->log($this->commandReceivedLevel, $message, $payload);
    }

    /**
     * {@inheritdoc}
     */
    public function logCommandSucceeded($command, $returnValue)
    {
        $message = $this->getSucceededMessage($command);
        $payload = $this->getPayload($command);

        $this->logger->log($this->commandSucceededLevel, $message, $payload);
    }

    /**
     * {@inheritdoc}
     */
    public function logCommandFailed($command, Exception $exception)
    {
        $message = $this->getFailedMessage($command);
        $payload = $this->getPayload($command);

        $payload = array_merge($payload, ['exception' => $exception]);

        $this->logger->log(
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
    private function getType($command)
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
    private function getClassName($command)
    {
        return substr(strrchr(get_class($command), '\\'), 1);
    }

    /**
     * @param $command
     *
     * @return array
     */
    private function getPayload($command)
    {
        if (!($command instanceof CommandInterface)
            && !($command instanceof QueryInterface)
            && !($command instanceof EventInterface)
        ) {
            return [];
        }

        $payload = $command->getPayload();

        return $this->preparePayload($payload);
    }

    /**
     * @param array $payload
     *
     * @return array
     */
    private function preparePayload(array $payload)
    {
        return array_map(function ($payload) {
            if (!($payload instanceof TransferObjectInterface)) {
                return $payload;
            }

            return $payload->getIdentifier();
        }, $payload);
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
    private function getSucceededMessage($command)
    {
        return $this->getType($command) . ' succeeded: ' . $this->getClassName($command);
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
