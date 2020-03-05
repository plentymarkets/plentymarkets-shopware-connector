<?php

namespace PlentymarketsAdapter\Client;

use Closure;
use PlentymarketsAdapter\Client\Iterator\Iterator;

interface ClientInterface
{
    /**
     * @param $method
     * @param $path
     * @param null $limit
     * @param null $offset
     */
    public function request($method, $path, array $params = [], $limit = null, $offset = null, array $options = []): array;

    /**
     * @param $path
     */
    public function getIterator($path, array $criteria = [], Closure $prepareFunction = null): Iterator;

    /**
     * @param $path
     */
    public function getTotal($path, array $criteria = []): int;
}
