<?php

namespace SystemConnector\BacklogService;

use Exception;
use Psr\Log\LoggerInterface;
use SystemConnector\BacklogService\Storage\BacklogServiceStorageInterface;
use SystemConnector\ServiceBus\Command\CommandInterface;
use Throwable;

class BacklogService implements BacklogServiceInterface
{
    const STATUS_OPEN = 'open';
    const STATUS_PROCESSED = 'processed';

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var BacklogServiceStorageInterface
     */
    private $storage;

    public function __construct(
        BacklogServiceStorageInterface $storage,
        LoggerInterface $logger
    ) {
        $this->storage = $storage;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function enqueue(CommandInterface $command)
    {
        try {
            $this->storage->enqueue($command);
        } catch (Exception $exception) {
            $this->logger->error($exception->getMessage());
        } catch (Throwable $exception) {
            $this->logger->error($exception->getMessage());
        }
    }

    /**
     * {@inheritdoc}
     */
    public function dequeue()
    {
        try {
            return $this->storage->dequeue();
        } catch (Exception $exception) {
            $this->logger->error($exception->getMessage());
        } catch (Throwable $exception) {
            $this->logger->error($exception->getMessage());
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getInfo()
    {
        try {
            return $this->storage->getInfo();
        } catch (Exception $exception) {
            $this->logger->error($exception->getMessage());
        } catch (Throwable $exception) {
            $this->logger->error($exception->getMessage());
        }

        return [];
    }
}
