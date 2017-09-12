<?php

namespace PlentymarketsAdapter\Client;

use Closure;
use PlentymarketsAdapter\Client\Iterator\Iterator;

/**
 * Interface ClientInterface.
 */
interface ClientInterface
{
    /**
     * @param string   $method
     * @param string   $path
     * @param array    $params
     * @param null|int $limit
     * @param null|int $offset
     * @param array    $options
     *
     * @return array
     */
    public function request($method, $path, array $params = [], $limit = null, $offset = null, array $options = []);

    /**
     * @param string       $path
     * @param array        $criteria
     * @param null|Closure $prepareFunction
     *
     * @return Iterator
     */
    public function getIterator($path, array $criteria = [], Closure $prepareFunction = null);

    /**
     * @param string $path
     * @param array  $criteria
     *
     * @return int
     */
    public function getTotal($path, array $criteria = []);
}
