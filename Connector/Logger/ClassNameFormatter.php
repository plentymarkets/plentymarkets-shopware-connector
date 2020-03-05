<?php

namespace SystemConnector\Logger;

use Exception;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use SystemConnector\ServiceBus\Command\CommandInterface;
use SystemConnector\ServiceBus\Query\QueryInterface;
use SystemConnector\TransferObject\TransferObjectInterface;

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
     * @param string $commandReceivedLevel
     * @param string $commandSucceededLevel
     * @param string $commandFailedLevel
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
        $message = $this->getReceivedMessage($command);
        $payload = $this->getPayload($command);

        $this->logger->log($this->commandReceivedLevel, $message, $payload);
    }

    /**
     * {@inheritdoc}
     */
    public function logCommandProcessed($command, $returnValue)
    {
        $message = $this->getProcessedMessage($command);
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
     * @param mixed $command
     */
    private function getType($command): string
    {
        if ($command instanceof CommandInterface) {
            return 'Command';
        }

        if ($command instanceof QueryInterface) {
            return 'Query';
        }

        return 'Undefined';
    }

    /**
     * @param mixed $command
     */
    private function getClassName($command): string
    {
        return substr(strrchr(get_class($command), '\\'), 1);
    }

    /**
     * @param mixed $command
     */
    private function getPayload($command): array
    {
        if (!($command instanceof CommandInterface) && !($command instanceof QueryInterface)) {
            return [];
        }

        $payload = $command->toArray();

        return $this->preparePayload($payload);
    }

    private function preparePayload(array $payload): array
    {
        return array_map(
            static function ($payload) {
                if (!($payload instanceof TransferObjectInterface)) {
                    return $payload;
                }

                return $payload->getIdentifier();
            }, $payload);
    }

    /**
     * @param mixed $command
     */
    private function getReceivedMessage($command): string
    {
        return $this->getType($command) . ' received: ' . $this->getClassName($command);
    }

    /**
     * @param mixed $command
     */
    private function getProcessedMessage($command): string
    {
        return $this->getType($command) . ' processed: ' . $this->getClassName($command);
    }

    /**
     * @param mixed $command
     */
    private function getFailedMessage($command): string
    {
        return $this->getType($command) . ' failed: ' . $this->getClassName($command);
    }
}
