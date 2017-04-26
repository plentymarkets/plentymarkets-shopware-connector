<?php

namespace PlentymarketsAdapter\Client\Iterator;

use Assert\Assertion;
use Iterator as BaseIterator;
use PlentymarketsAdapter\Client\Client;

/**
 * Class Iterator
 */
class Iterator implements BaseIterator
{
    /**
     * @var Client
     */
    private $client;

    /**
     * @var int
     */
    private $index = 0;

    /**
     * @var int
     */
    private $limit = 50;

    /**
     * @var int
     */
    private $offset = 0;

    /**
     * @var array
     */
    private $page = [];

    /**
     * @var array
     */
    private $criteria;

    /**
     * @var
     */
    private $path;

    /**
     * ResourceIterator constructor.
     *
     * @param string $path
     * @param Client $client
     * @param array  $criteria
     */
    public function __construct($path, Client $client, array $criteria = [])
    {
        Assertion::string($path);

        $this->client = $client;
        $this->criteria = $criteria;
        $this->path = $path;
    }

    /**
     * {@inheritdoc}
     */
    public function current()
    {
        return $this->page[$this->index];
    }

    /**
     * {@inheritdoc}
     */
    public function next()
    {
        ++$this->index;

        if (!$this->valid()) {
            $this->offset += $this->limit;

            $this->loadPage($this->criteria, $this->limit, $this->offset);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function valid()
    {
        return array_key_exists($this->index, $this->page);
    }

    /**
     * {@inheritdoc}
     */
    public function key()
    {
        return $this->index;
    }

    /**
     * {@inheritdoc}
     */
    public function rewind()
    {
        $this->loadPage($this->criteria, $this->limit, 0);

        $this->offset = 0;
        $this->index = 0;
    }

    /**
     * @param array $criteria
     * @param int $limit
     * @param int $offset
     */
    private function loadPage(array $criteria = [], $limit = 0, $offset = 0)
    {
        $result = $this->client->request('GET', $this->path, $criteria, $limit, $offset);

        foreach ($result as $key => $item) {
            $this->page[$this->index + $key] = $item;
        }
    }
}
