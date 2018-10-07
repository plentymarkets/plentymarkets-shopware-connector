<?php

namespace PlentymarketsAdapter\Client;

use Assert\Assertion;
use Closure;
use Exception;
use GuzzleHttp\ClientInterface as GuzzleClientInterface;
use GuzzleHttp\Exception\ClientException;
use PlentyConnector\Connector\ConfigService\ConfigServiceInterface;
use PlentymarketsAdapter\Client\Exception\InvalidCredentialsException;
use PlentymarketsAdapter\Client\Exception\InvalidResponseException;
use PlentymarketsAdapter\Client\Iterator\Iterator;

class Client implements ClientInterface
{
    /**
     * @var GuzzleClientInterface
     */
    private $connection;

    /**
     * @var ConfigServiceInterface
     */
    private $configService;

    /**
     * @var null|string
     */
    private $accessToken;

    /**
     * @var null|string
     */
    private $refreshToken;

    /**
     * @param GuzzleClientInterface  $connection
     * @param ConfigServiceInterface $configService
     */
    public function __construct(
        GuzzleClientInterface $connection,
        ConfigServiceInterface $configService
    ) {
        $this->connection = $connection;
        $this->configService = $configService;
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator($path, array $criteria = [], Closure $prepareFunction = null)
    {
        return new Iterator($path, $this, $criteria, $prepareFunction);
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
     * {@inheritdoc}
     */
    public function request($method, $path, array $params = [], $limit = null, $offset = null, array $options = [])
    {
        static $retries;

        if (null === $retries) {
            $retries = 0;
        }

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

            if (null === $response) {
                throw InvalidResponseException::fromParams($method, $path, $options);
            }

            $body = $response->getBody();

            if (null === $body) {
                throw InvalidResponseException::fromParams($method, $path, $options);
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

            $retries = 0;

            return $result;
        } catch (ClientException $exception) {
            if ($this->accessToken !== null && !$this->isLoginRequired($path) && null !== $exception->getResponse() && $exception->getResponse()->getStatusCode() === 401) {
                // retry with fresh accessToken
                $this->accessToken = null;

                return $this->request($method, $path, $params, $limit, $offset);
            }

            if ($exception->hasResponse() && $exception->getResponse()) {
                throw new ClientException(
                    $exception->getMessage() . ' - ' . $exception->getResponse()->getBody(),
                    $exception->getRequest(),
                    $exception->getResponse(),
                    $exception->getPrevious()
                );
            }

            throw $exception;
        } catch (Exception $exception) {
            if ($retries < 3) {
                sleep(10);

                ++$retries;

                return $this->request($method, $path, $params, $limit, $offset);
            }

            throw $exception;
        }
    }

    /**
     * @param string $path
     *
     * @return bool
     */
    private function isLoginRequired($path)
    {
        if ($path === 'login') {
            return false;
        }

        if (null !== $this->accessToken) {
            return false;
        }

        return true;
    }

    private function login()
    {
        if (null === $this->configService->get('rest_username') || null === $this->configService->get('rest_password')) {
            throw new InvalidCredentialsException('invalid creddentials');
        }

        $login = $this->request('POST', 'login', [
            'username' => $this->configService->get('rest_username'),
            'password' => $this->configService->get('rest_password'),
        ]);

        $this->accessToken = $login['accessToken'];
        $this->refreshToken = $login['refreshToken'];
    }

    /**
     * @param int   $limit
     * @param int   $offset
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
     * @param int $limit
     * @param int $offset
     *
     * @return int
     */
    private function getPage($limit, $offset)
    {
        $page = 1.0;

        if (null !== $offset) {
            $page = (floor($offset / $limit) + 1);
        }

        return (int) $page;
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
            $base_uri = $this->getBaseUri($this->configService->get('rest_url'));
        } else {
            $base_uri = $this->getBaseUri($options['base_uri']);
        }

        return $base_uri . $path;
    }

    /**
     * @param string $url
     *
     * @return string
     */
    private function getBaseUri($url)
    {
        if (null === $url) {
            throw new InvalidCredentialsException('invalid creddentials');
        }

        $parts = parse_url($url);

        return sprintf('%s://%s/%s/', $parts['scheme'], $parts['host'], 'rest');
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

        if (array_key_exists('itemsPerPage', $options)) {
            $params['itemsPerPage'] = $options['itemsPerPage'];
        }

        if (array_key_exists('page', $options)) {
            $params['page'] = $options['page'];
        }

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
            $requestOptions['timeout'] = 60;
        }

        if (!array_key_exists('exceptions', $options)) {
            $requestOptions['exceptions'] = true;
        }

        return $requestOptions;
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
}
