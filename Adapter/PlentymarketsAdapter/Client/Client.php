<?php

namespace PlentymarketsAdapter\Client;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\ClientException;
use PlentyConnector\Connector\Config\Config;
use PlentymarketsAdapter\Client\Exception\InvalidCredentialsException;
use PlentymarketsAdapter\Client\Iterator\Iterator;

/**
 * RepsonseModifier example
 *
 * Class Client
 *
 * @package PlentyConnector\Adapter\Plentymarkets\Client
 */
class Client implements ClientInterface
{
    /**
     * @var GuzzleClient $connection
     */
    private $connection;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var string
     */
    private $accessToken;

    /**
     * @var string
     */
    private $refreshToken;

    /**
     * Client constructor.
     *
     * @param GuzzleClient $connection
     * @param Config $config
     */
    public function __construct(GuzzleClient $connection, Config $config)
    {
        $this->connection = $connection;
        $this->config = $config;
    }

    /**
     * TODO: finalize Exceptions
     *
     * @inheritdoc
     */
    public function request($method, $path, array $criteria = [], $limit = null, $offset = null)
    {
        if ($this->isLoginRequired($path)) {
            $this->login();
        }

        $options = [
            'base_uri' => $this->getBaseUri($this->config->get('rest_url')),
            'headers' => $this->getHeaders($path),
        ];

        if ($method === 'GET') {
            $options['query'] = $this->getParams($criteria, $limit, $offset);
        } else {
            $options['json'] = $this->getParams($criteria, $limit, $offset);
        }

        try {
            $response = $this->connection->request($method, $path, $options);
        } catch (ClientException $exception) {
            if ($exception->hasResponse() && $exception->getResponse()->getStatusCode() === 401) {
                if ($path === 'login') {
                    throw new InvalidCredentialsException();
                } else {
                    // retry with fresh accessToken
                    $this->accessToken = null;

                    return $this->request($method, $path, $criteria, $limit, $offset);
                }
            } else {
                // unknown exception, throw up
                throw $exception;
            }
        }

        $result = json_decode($response->getBody(), true);

        if (!array_key_exists('entries', $result)) {
            $entries = $result;
        } else {
            $entries = $result['entries'];
        }

        return $entries;
    }

    /**
     * @param $path
     *
     * @return bool
     */
    private function isLoginRequired($path)
    {
        return $path !== 'login' && null === $this->accessToken;
    }

    /**
     * @throws \Exception
     */
    private function login()
    {
        $login = $this->request('POST', 'login', [
            'username' => $this->config->get('rest_username'),
            'password' => $this->config->get('rest_password'),
        ]);

        $this->accessToken = $login['accessToken'];
        $this->refreshToken = $login['refreshToken'];
    }

    /**
     * @param $url
     *
     * @return string
     */
    private function getBaseUri($url)
    {
        $parts = parse_url($url);

        return sprintf('%s://%s/%s/', $parts['scheme'], $parts['host'], 'rest');
    }

    /**
     * @param string $path
     *
     * @return array
     */
    private function getHeaders($path)
    {
        $headers = [
            'Content-Type' => 'application/json',
            'Accept' => 'application/x.plentymarkets.v1+json'
        ];

        if ($path !== 'login') {
            $headers['Authorization'] = 'Bearer ' . $this->accessToken;
        }

        return $headers;
    }

    /**
     * Adds the required parameters to the params array
     *
     * @param array $criteria
     * @param null $limit
     * @param null $offset
     *
     * @return array the params array
     */
    private function getParams(array $criteria = [], $limit = null, $offset = null)
    {
        $page = ceil($offset / $limit);

        $params = [
            'itemsPerPage' => null === $limit ? 0 : $limit,
            'page' => $page,
        ];

        $params = array_merge($params, $criteria);

        return $params;
    }

    /**
     * @inheritdoc
     */
    public function getIterator($path, array $criteria = [])
    {
        return new Iterator($path, $this, $criteria);
    }
}
