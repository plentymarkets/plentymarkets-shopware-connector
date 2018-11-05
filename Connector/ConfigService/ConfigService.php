<?php

namespace SystemConnector\ConfigService;

use Exception;
use Psr\Log\LoggerInterface;
use SystemConnector\ConfigService\Storage\ConfigServiceStorageInterface;
use Throwable;

class ConfigService implements ConfigServiceInterface
{
    /**
     * @var ConfigServiceStorageInterface
     */
    private $storage;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        ConfigServiceStorageInterface $storage,
        LoggerInterface $logger
    ) {
        $this->storage = $storage;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function getAll()
    {
        try {
            return $this->storage->getAll();
        } catch (Exception $exception) {
            $this->logger->error($exception->getMessage());
        } catch (Throwable $exception) {
            $this->logger->error($exception->getMessage());
        }

        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function get($key, $default = null)
    {
        try {
            return $this->storage->get($key, $default);
        } catch (Exception $exception) {
            $this->logger->error($exception->getMessage());
        } catch (Throwable $exception) {
            $this->logger->error($exception->getMessage());
        }

        return '';
    }

    /**
     * {@inheritdoc}
     */
    public function set($key, $value)
    {
        try {
            $this->storage->set($key, $value);
        } catch (Exception $exception) {
            $this->logger->error($exception->getMessage());
        } catch (Throwable $exception) {
            $this->logger->error($exception->getMessage());
        }
    }
}
