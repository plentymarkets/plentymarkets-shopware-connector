<?php

namespace PlentymarketsAdapter\Client;

use Assert\Assertion;
use Closure;
use Exception;
use PlentyConnector\Connector\ConfigService\ConfigServiceInterface;
use PlentymarketsAdapter\Client\Exception\InvalidCredentialsException;
use PlentymarketsAdapter\Client\Exception\InvalidResponseException;
use PlentymarketsAdapter\Client\Exception\LimitReachedException;
use PlentymarketsAdapter\Client\Exception\LoginExpiredException;
use PlentymarketsAdapter\Client\Iterator\Iterator;
use Psr\Log\LoggerInterface;
use RuntimeException;

class Client implements ClientInterface
{
    /**
     * @var ConfigServiceInterface
     */
    private $config;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var null|string
     */
    private $accessToken;

    public function __construct(
        ConfigServiceInterface $config,
        LoggerInterface $logger
    ) {
        $this->config = $config;
        $this->logger = $logger;
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

        try {
            $requestUrl = $this->getUrl($path, $options);

            if ($path !== 'login') {
              /*  for ($i = 0; $i < 300; ++$i) {
                    $this->curlRequest($requestUrl, $method, $path, $params, $limit, $offset);
                }*/
            }

            $response = $this->curlRequest($requestUrl, $method, $path, $params, $limit, $offset);

            if (null === $response) {
                throw InvalidResponseException::fromParams($method, $path, $options);
            }

            if (!array_key_exists('plainResponse', $options) || !$options['plainResponse']) {
                // Hack to check if the right page is returned from the api
                if (array_key_exists('page', $response) && $response['page'] !== $this->getPage($limit, $offset)) {
                    $response['entries'] = [];
                }

                if (array_key_exists('entries', $response)) {
                    $response = $response['entries'];
                }
            }

            $retries = 0;

            return $response;
        } catch (Exception $exception) {
            if ($retries < 4) {
                if ($exception instanceof LoginExpiredException) {
                    $this->accessToken = null;
                }

                if ($exception instanceof LimitReachedException) {
                    sleep($exception->getRetryAfter());
                } else {
                    sleep(10);
                }

                ++$retries;

                return $this->request($method, $path, $params, $limit, $offset);
            }

            throw $exception;
        }
    }

    private function curlRequest($requestUrl, $method, $path, $params, $limit, $offset)
    {
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_USERAGENT, $this->getUserAgent());
        curl_setopt($curl, CURLOPT_HTTPHEADER, $this->getHeaders($path));
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, 60);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 60);

        // Retry-After: -1536195196
        $headers = [];
        curl_setopt($curl, CURLOPT_HEADERFUNCTION, function ($curl, $header) use (&$headers) {
            $headers[] = $header;

            return strlen($header);
        });

        if (null !== $limit) {
            $params['itemsPerPage'] = (int) $limit;
        }

        if (null !== $offset) {
            $params['page'] = $this->getPage($limit, $offset);
        }

        $method = strtoupper($method);
        if ($method === 'POST') {
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($params, JSON_PRETTY_PRINT));
        } elseif ($method === 'GET') {
            $requestUrl = $requestUrl . '?' . http_build_query($params);
        }

        curl_setopt($curl, CURLOPT_URL, $requestUrl);

        $response = curl_exec($curl);
        $info = curl_getinfo($curl);
        $errno = curl_errno($curl);

        if ($errno !== CURLE_OK) {
            throw new RuntimeException('client error');
        }

        if ($info['http_code'] === 200) {
            return json_decode($response, true);
        }

        if ($info['http_code'] === 401) {
            throw new LoginExpiredException();
        }

        if ($info['http_code'] === 429) {
            throw new LimitReachedException(20);
        }

        throw new RuntimeException('client error');
    }

    /**
     * @param string $path
     *
     * @return bool
     */
    private function isLoginRequired($path)
    {
        if ('login' === $path) {
            return false;
        }

        if (null !== $this->accessToken) {
            return false;
        }

        return true;
    }

    private function login()
    {
        if (null === $this->config->get('rest_username') || null === $this->config->get('rest_password')) {
            throw new InvalidCredentialsException('invalid creddentials');
        }

        $login = $this->request('POST', 'login', [
            'username' => $this->config->get('rest_username'),
            'password' => $this->config->get('rest_password'),
        ]);

        $this->accessToken = $login['accessToken'];
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
            $base_uri = $this->getBaseUri($this->config->get('rest_url'));
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

        if (empty($parts['scheme'])) {
            $parts['scheme'] = 'http';
        }

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
            'Content-Type: application/json',
            'Accept: application/x.plentymarkets.v1+json',
            'cache-control: no-cache',
            'user-agent: ' . $this->getUserAgent(),
        ];

        if ('login' !== $path) {
            $headers[] = 'Authorization: Bearer ' . $this->accessToken;
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
