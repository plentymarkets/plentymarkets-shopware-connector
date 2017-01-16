<?php

namespace PlentymarketsAdapter\Client;

use Iterator;
use PlentyConnector\Adapter\PlentymarketsAdapter\Client\Exception\InvalidResponseException;
use PlentymarketsAdapter\Client\Exception\InvalidCredentialsException;
use UnexpectedValueException;

/**
 * Interface ClientInterface.
 */
interface ClientInterface
{
    /**
     * @param string $method
     * @param string $path
     * @param array $criteria
     * @param int|null $limit
     * @param int|null $offset
     * @param array $options
     *
     * @return array
     *
     * @throws InvalidCredentialsException
     * @throws InvalidResponseException
     */
    public function request($method, $path, array $criteria = [], $limit = null, $offset = null, array $options = []);

    /**
     * @param $path
     * @param array $criteria
     *
     * @return Iterator
     *
     * @throws UnexpectedValueException
     */
    public function getIterator($path, array $criteria = []);
}
