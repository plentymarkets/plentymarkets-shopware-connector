<?php

namespace PlentymarketsAdapter\Client;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\ClientInterface as GuzzleClientInterface;
use GuzzleHttp\Exception\ClientException;
use PlentyConnector\Connector\Config\ConfigServiceInterface;
use PlentymarketsAdapter\Client\Exception\InvalidCredentialsException;
use PlentymarketsAdapter\Client\Iterator\Iterator;

/**
 * RepsonseModifier example.
 *
 * Class Client
 */
class Client implements ClientInterface
{
    /**
     * @var GuzzleClientInterface
     */
    private $connection;

    /**
     * @var ConfigServiceInterface
     */
    private $config;

    /**
     * @var string|null
     */
    private $accessToken;

    /**
     * @var string|null
     */
    private $refreshToken;

    /**
     * Client constructor.
     *
     * @param GuzzleClient $connection
     * @param ConfigServiceInterface $config
     */
    public function __construct(GuzzleClient $connection, ConfigServiceInterface $config)
    {
        $this->connection = $connection;
        $this->config = $config;
    }

    /**
     * TODO: simplify login handling
     *
     * {@inheritdoc}
     */
    public function request($method, $path, array $params = [], $limit = null, $offset = null)
    {
        if ($this->isLoginRequired($path)) {
            $this->login();
        }

        $options = [
            'base_uri' => $this->getBaseUri($this->config->get('rest_url')),
            'headers' => $this->getHeaders($path),
        ];

        if ($method === 'GET') {
            $options['query'] = $params;
        } else {
            $options['json'] = $params;
        }

        if (null !== $limit) {
            $params['itemsPerPage'] = (int)$limit;
        }

        if (null !== $offset) {
            $page = floor($offset / $limit);

            $params['page'] = $page !== 0 ? $page : 1;
        }

        try {
            $response = $this->connection->request($method, $path, $options);

            $result = json_decode($response->getBody(), true);

            if (!array_key_exists('entries', $result)) {
                $entries = $result;
            } else {
                $sliceOffset = $offset - (($params['page'] - 1) * $params['itemsPerPage']);
                $entries = array_slice($result['entries'], $sliceOffset, $limit >= 0 ? $limit : null);
            }

            return $entries;
        } catch (ClientException $exception) {
            if ($exception->hasResponse() && $exception->getResponse()->getStatusCode() === 401) {
                if ($path === 'login') {
                    throw new InvalidCredentialsException();
                } else {
                    // retry with fresh accessToken
                    $this->accessToken = null;

                    return $this->request($method, $path, $params, $limit, $offset);
                }
            } else {
                // unknown exception, throw up
                throw $exception;
            }
        }
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
            'Accept' => 'application/x.plentymarkets.v1+json',
        ];

        if ($path !== 'login') {
            $headers['Authorization'] = 'Bearer ' . $this->accessToken;
        }

        return $headers;
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator($path, array $criteria = [])
    {
        return new Iterator($path, $this, $criteria);
    }
}
