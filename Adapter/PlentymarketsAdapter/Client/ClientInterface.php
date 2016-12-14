<?php

namespace PlentymarketsAdapter\Client;

use Exception;
use GuzzleHttp\Exception\ClientException;
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
     * @param array  $criteria
     * @param null   $limit
     * @param null   $offset
     *
     * @return array
     *
     * @throws ClientException
     * @throws Exception
     * @throws InvalidCredentialsException
     * @throws InvalidResponseException
     */
    public function request($method, $path, array $criteria = [], $limit = null, $offset = null);

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
