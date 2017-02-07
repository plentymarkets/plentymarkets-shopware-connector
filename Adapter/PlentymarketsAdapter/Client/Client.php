<?php

namespace PlentymarketsAdapter\Client;

use Assert\Assertion;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\ClientInterface as GuzzleClientInterface;
use GuzzleHttp\Exception\ClientException;
use PlentyConnector\Adapter\PlentymarketsAdapter\Client\Exception\InvalidResponseException;
use PlentyConnector\Connector\ConfigService\ConfigServiceInterface;
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
     * @var string
     */
    private $environment;

    /**
     * Client constructor.
     *
     * @param GuzzleClient           $connection
     * @param ConfigServiceInterface $config
     * @param $environment
     */
    public function __construct(
        GuzzleClient $connection,
        ConfigServiceInterface $config,
        $environment
    ) {
        $this->connection = $connection;
        $this->config = $config;
        $this->environment = $environment;
    }

    /**
     * TODO: simplify login handling.
     *
     * {@inheritdoc}
     */
    public function request($method, $path, array $params = [], $limit = null, $offset = null, array $options = [])
    {
        Assertion::nullOrInteger($limit);
        Assertion::nullOrInteger($offset);
        Assertion::isArray($options);

        if ($this->isLoginRequired($path)) {
            $this->login();
        }

        $method = strtoupper($method);

        $options = $this->getOptions($limit, $offset, $options);
        $url = $this->getUrl($path, $options);
        $requestOptions = $this->getRequestOptions($method, $path, $params, $options);

        $request = $this->connection->createRequest($method, $url, $requestOptions);

        try {
            $response = $this->connection->send($request);

            $body = $response->getBody();

            if (null === $body) {
                // throw
            }

            $result = json_decode($body->getContents(), true);

            if (null === $result) {
                throw InvalidResponseException::fromParams($method, $path, $options);
            }

            if (!$options['plainResponse']) {
                // Hack to check if the right page is returned from the api
                if (array_key_exists('page', $result) && $result['page'] !== $this->getPage($limit, $offset)) {
                    $result['entries'] = [];
                }

                if (array_key_exists('entries', $result)) {
                    $result = $result['entries'];
                }
            }

            return $result;
        } catch (ClientException $exception) {
            if ($exception->hasResponse() && $exception->getResponse()->getStatusCode() === 401 && !$this->isLoginRequired($path) && $this->accessToken != null) {
                // retry with fresh accessToken
                $this->accessToken = null;

                return $this->request($method, $path, $params, $limit, $offset);
            }
                // generic exception
                throw $exception;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator($path, array $criteria = [])
    {
        return new Iterator($path, $this, $criteria);
    }

    /**
     * {@inheritdoc}
     */
    public function getTotal($path, array $criteria = [])
    {
        $options = [
            'plainResponse' => true,
        ];

        $response = $this->request('GET', $path, $criteria, null, null, $options);

        if (array_key_exists('totalsCount', $response)) {
            return (int) $response['totalsCount'];
        }

        if (array_key_exists('entries', $response)) {
            $response = $response['entries'];
        }

        return count($response);
    }

    /**
     * @param $path
     *
     * @return bool
     */
    private function isLoginRequired($path)
    {
        if ($this->environment === 'testing') {
            return false;
        }

        if ($path === 'login') {
            return false;
        }

        if (null !== $this->accessToken) {
            return false;
        }

        return true;
    }

    /**
     * @throws InvalidCredentialsException
     */
    private function login()
    {
        if (null === $this->config->get('rest_username') || null === $this->config->get('rest_password')) {
            throw new InvalidCredentialsException();
        }

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
     * @throws InvalidCredentialsException
     *
     * @return string
     */
    private function getBaseUri($url)
    {
        if (null === $url) {
            throw new InvalidCredentialsException();
        }

        $parts = parse_url($url);

        return sprintf('https://%s/%s/', $parts['host'], 'rest');
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
            'user-agent' => $this->getUserAgent(),
        ];

        if ($path !== 'login') {
            $headers['Authorization'] = 'Bearer ' . $this->accessToken;
        }

        return $headers;
    }

    /**
     * @return string
     */
    private function getUserAgent()
    {
        return 'Shopware/PlentyConnector/2.0/Rest/v1';
    }

    /**
     * @param $limit
     * @param $offset
     * @param array $options
     *
     * @return array
     */
    private function getOptions($limit, $offset, array $options)
    {
        if (!array_key_exists('plainResponse', $options)) {
            $options['plainResponse'] = false;
        }

        if (null !== $limit) {
            $options['itemsPerPage'] = (int) $limit;
        }

        if (null !== $offset) {
            $options['page'] = $this->getPage($limit, $offset);
        }

        return $options;
    }

    /**
     * @param string $method
     * @param string $path
     * @param array  $params
     * @param array  $options
     *
     * @return array
     */
    private function getRequestOptions($method, $path, array $params, array $options)
    {
        Assertion::string($method);
        Assertion::inArray($method, [
            'POST',
            'PUT',
            'DELETE',
            'GET',
        ]);
        Assertion::isArray($params);

        $requestOptions = [];

        if ($method === 'GET') {
            $requestOptions['query'] = $params;
        } else {
            $requestOptions['json'] = $params;
        }

        if (!array_key_exists('headers', $options)) {
            $requestOptions['headers'] = $this->getHeaders($path);
        }

        if (!array_key_exists('connect_timeout', $options)) {
            $requestOptions['connect_timeout'] = 30;
        }

        if (!array_key_exists('timeout', $options)) {
            $requestOptions['timeout'] = 30;
        }

        if (!array_key_exists('exceptions', $options)) {
            $requestOptions['exceptions'] = true;
        }

        return $requestOptions;
    }

    /**
     * @param string $path
     * @param array  $options
     *
     * @return string
     */
    private function getUrl($path, array $options = [])
    {
        Assertion::string($path);
        Assertion::notBlank($path);

        if (!array_key_exists('base_uri', $options)) {
            $base_uri = $this->getBaseUri($this->config->get('rest_url'));
        } else {
            $base_uri = $this->getBaseUri($options['base_uri']);
        }

        return $base_uri . $path;
    }

    /**
     * @param int $limit
     * @param int $offset
     *
     * @return int
     */
    private function getPage($limit, $offset)
    {
        $page = 1;

        if (null !== $offset) {
            $page = (int) (floor($offset / $limit) + 1);
        }

        return $page;
    }
}
