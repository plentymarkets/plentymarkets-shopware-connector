<?php

namespace SystemConnector\BacklogService;

use SystemConnector\BacklogService\Storage\BacklogServiceStorageInterface;
use SystemConnector\ServiceBus\Command\CommandInterface;
use Traversable;

class BacklogService implements BacklogServiceInterface
{
    /**
     * @var BacklogServiceStorageInterface[]|Traversable
     */
    private $storages;

    /**
     * @param BacklogServiceStorageInterface[]|Traversable $storage
     */
    public function __construct(Traversable $storage)
    {
        $this->storages = iterator_to_array($storage);
    }

    /**
     * {@inheritdoc}
     */
    public function enqueue(CommandInterface $command)
    {
        $storage = reset($this->storages);

        $storage->enqueue($command);
    }

    /**
     * {@inheritdoc}
     */
    public function dequeue()
    {
        $storage = reset($this->storages);

        return $storage->dequeue();
    }

    /**
     * {@inheritdoc}
     */
    public function getInfo() :array
    {
        $storage = reset($this->storages);

        return $storage->getInfo();
    }
}
