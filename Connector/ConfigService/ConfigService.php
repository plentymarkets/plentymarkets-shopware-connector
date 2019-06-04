<?php

namespace SystemConnector\ConfigService;

use SystemConnector\ConfigService\Storage\ConfigServiceStorageInterface;
use Traversable;

class ConfigService implements ConfigServiceInterface
{
    /**
     * @var ConfigServiceStorageInterface[]
     */
    private $storages;

    public function __construct(Traversable $storage)
    {
        $this->storages = iterator_to_array($storage);
    }

    /**
     * {@inheritdoc}
     */
    public function getAll(): array
    {
        $storage = reset($this->storages);

        return $storage->getAll();
    }

    /**
     * {@inheritdoc}
     */
    public function get($key, $default = null)
    {
        $storage = reset($this->storages);

        $result = $storage->get($key);

        if ($result !== null) {
            return $result;
        }

        return $default;
    }

    /**
     * {@inheritdoc}
     */
    public function set($key, $value)
    {
        $storage = reset($this->storages);

        $storage->set($key, $value);
    }
}
