<?php

namespace PlentymarketsAdapter\Client;

use Closure;
use PlentymarketsAdapter\Client\Iterator\Iterator;

interface ClientInterface
{
    /**
     * @param $method
     * @param $path
     * @param array $params
     * @param null  $limit
     * @param null  $offset
     * @param array $options
     *
     * @return array
     */
    public function request($method, $path, array $params = [], $limit = null, $offset = null, array $options = []): array;

    /**
     * @param $path
     * @param array        $criteria
     * @param null|Closure $prepareFunction
     *
     * @return Iterator
     */
    public function getIterator($path, array $criteria = [], Closure $prepareFunction = null): Iterator;

    /**
     * @param $path
     * @param array $criteria
     *
     * @return int
     */
    public function getTotal($path, array $criteria = []): int;
}
