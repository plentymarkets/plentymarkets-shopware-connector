<?php

namespace PlentymarketsAdapter\Client\Iterator;

use Assert\Assertion;
use Assert\AssertionFailedException;
use Closure;
use Countable;
use Iterator as BaseIterator;
use PlentymarketsAdapter\Client\Client;
use PlentymarketsAdapter\Client\Exception\InvalidCredentialsException;
use Throwable;

class Iterator implements BaseIterator, Countable
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
    private $limit = 200;

    /**
     * @var int
     */
    private $offset = 0;

    /**
     * @var array
     */
    private $data = [];

    /**
     * @var array
     */
    private $criteria;

    /**
     * @var string
     */
    private $path;

    /**
     * @var Closure
     */
    private $prepareFunction;

    /**
     * @var bool
     */
    private $isLastPage = false;

    /**
     * @param $path
     *
     * @throws AssertionFailedException
     */
    public function __construct($path, Client $client, array $criteria = [], Closure $prepareFunction = null)
    {
        Assertion::string($path);

        $this->client = $client;
        $this->criteria = $criteria;
        $this->path = $path;
        $this->prepareFunction = $prepareFunction;

        $this->limit = $this->client->getItemsPerPage();
    }

    /**
     * {@inheritdoc}
     */
    public function current()
    {
        $element = $this->data[$this->index];

        unset($this->data[$this->index]);

        return $element;
    }

    /**
     * {@inheritdoc}
     */
    public function next()
    {
        ++$this->index;

        if (!$this->isLastPage && !$this->valid()) {
            $this->offset += $this->limit;

            $this->loadPage($this->criteria, $this->limit, $this->offset);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function valid()
    {
        return array_key_exists($this->index, $this->data);
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
        $this->loadPage($this->criteria, $this->limit);

        $this->offset = 0;
        $this->index = 0;
    }

    /**
     * @throws AssertionFailedException
     * @throws InvalidCredentialsException
     * @throws Throwable
     */
    public function count(): int
    {
        return $this->client->getTotal($this->path, $this->criteria);
    }

    /**
     * @param int $limit
     * @param int $offset
     */
    private function loadPage(array $criteria = [], $limit = 0, $offset = 0)
    {
        $result = $this->client->request('GET', $this->path, $criteria, $limit, $offset);

        if (null !== $this->prepareFunction) {
            $result = call_user_func($this->prepareFunction, $result);
        }

        $itemsPerPage = $this->client->getItemsPerPage();

        if ($itemsPerPage !== $this->limit) {
            $this->limit = (int) $itemsPerPage;
        }

        if (count($result) !== $this->limit) {
            $this->isLastPage = true;
        }

        foreach ($result as $key => $item) {
            $this->data[$this->index + $key] = $item;
        }
    }
}
