<?php

namespace PlentymarketsAdapter\Client;

use Assert\Assertion;
use Closure;
use Exception;
use PlentymarketsAdapter\Client\Exception\InvalidCredentialsException;
use PlentymarketsAdapter\Client\Exception\InvalidResponseException;
use PlentymarketsAdapter\Client\Exception\LimitReachedException;
use PlentymarketsAdapter\Client\Exception\LoginExpiredException;
use PlentymarketsAdapter\Client\Iterator\Iterator;
use Psr\Log\LoggerInterface;
use RuntimeException;
use SystemConnector\ConfigService\ConfigServiceInterface;
use Throwable;

class Client implements ClientInterface
{
    /**
     * @var ConfigServiceInterface
     */
    private $configService;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * api access token
     *
     * @var null|string
     */
    private $accessToken;

    /**
     * retries used for the current request
     *
     * @var int
     */
    private $retries = 0;

    public function __construct(
        ConfigServiceInterface $config,
        LoggerInterface $logger
    ) {
        $this->configService = $config;
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

        if (isset($response['totalsCount'])) {
            return (int) $response['totalsCount'];
        }

        if (isset($response['entries'])) {
            $response = $response['entries'];
        }

        return count($response);
    }

    /**
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

        try {
            $requestUrl = $this->getUrl($path, $options);
            $response = $this->curlRequest($requestUrl, $method, $path, $params, $limit, $offset);

            if (null === $response) {
                throw InvalidResponseException::fromParams($method, $path, $options);
            }

            $this->retries = 0;

            return $this->prepareResponse($limit, $offset, $options, $response);
        } catch (Exception $exception) {
            return $this->handleRequestException($exception, $method, $path, $params, $limit, $offset);
        } catch (Throwable $exception) {
            return $this->handleRequestException($exception, $method, $path, $params, $limit, $offset);
        }
    }

    /**
     * @param string $requestUrl
     * @param string $method
     * @param string $path
     * @param array  $params
     * @param int    $limit
     * @param int    $offset
     *
     * @return array
     */
    private function curlRequest($requestUrl, $method, $path, $params, $limit, $offset)
    {
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_USERAGENT, $this->getUserAgent());
        curl_setopt($curl, CURLOPT_HTTPHEADER, $this->getHeaders($path));
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, 60);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 60);

        $headers = [];
        curl_setopt($curl, CURLOPT_HEADERFUNCTION, function ($curl, $header) use (&$headers) {
            if (stripos($header, 'X-Plenty') === false) {
                return strlen($header);
            }

            $name = substr($header, 0, strpos($header, ':'));
            $value = substr($header, strpos($header, ':') + 1);

            $headers[$name] = (int) trim($value);

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
            throw new RuntimeException('curl client error:' . $errno);
        }

        $this->handeRateLimits($headers);

        if ($info['http_code'] === 401) {
            throw new LoginExpiredException();
        }

        if ($info['http_code'] === 200) {
            $json = json_decode($response, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw InvalidResponseException::fromParams(
                    $method,
                    $path,
                    $params
                );
            }

            return $json;
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
        if (null === $this->configService->get('rest_username') || null === $this->configService->get('rest_password')) {
            throw new InvalidCredentialsException('invalid creddentials');
        }

        $login = $this->request('POST', 'login', [
            'username' => $this->configService->get('rest_username'),
            'password' => $this->configService->get('rest_password'),
        ]);

        if (empty($login['accessToken'])) {
            throw new RuntimeException('could not read access token from login response');
        }

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

        if (!isset($options['base_uri']) && !isset($options['foreign'])) {
            $base_uri = $this->getBaseUri($this->configService->get('rest_url'));
        } elseif ($options['foreign']) {
            $base_uri = str_replace('/rest', '', $this->getBaseUri($this->configService->get('rest_url')));
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

    /**
     * @param Throwable $exception
     * @param string    $method
     * @param string    $path
     * @param array     $params
     * @param int       $limit
     * @param int       $offset
     *
     * @return array
     */
    private function handleRequestException(Throwable $exception, $method, $path, array $params, $limit, $offset)
    {
        if ($this->retries >= 4) {
            $this->retries = 0;

            throw $exception;
        }

        ++$this->retries;

        if ($exception instanceof LoginExpiredException) {
            $this->accessToken = null;
        }

        if ($exception instanceof LimitReachedException) {
            if ($exception->getRetryAfter() > 60 * 15) {
                $this->logger->error('rate limit reached and retry after value is too high, aborting');
            } else {
                $this->logger->warning(
                    sprintf('rate limit reached, retrying in %s seconds', $exception->getRetryAfter())
                );

                sleep($exception->getRetryAfter());
            }
        } else {
            sleep(10);
        }

        return $this->request($method, $path, $params, $limit, $offset);
    }

    /**
     * @param int   $limit
     * @param int   $offset
     * @param array $options
     * @param array $response
     *
     * @return array
     */
    private function prepareResponse($limit, $offset, array $options, array $response)
    {
        if (!isset($options['plainResponse']) || !$options['plainResponse']) {
            // Hack to ensure that the correct page is returned from the api
            if (isset($response['page']) && $response['page'] !== $this->getPage($limit, $offset)) {
                $response['entries'] = [];
            }

            if (isset($response['entries'])) {
                $response = $response['entries'];
            }
        }

        return $response;
    }

    /**
     * @param array $headers
     */
    private function handeRateLimits(array $headers)
    {
        $limitHeaders = [
            'X-Plenty-Global-Long-Period',
            'X-Plenty-Global-Short-Period',
            'X-Plenty-Route',
        ];

        $retryAfter = 0;

        foreach ($limitHeaders as $header) {
            $callsLeftHeader = $header . '-Calls-Left';

            if (!isset($headers[$callsLeftHeader]) || $headers[$callsLeftHeader] > 0) {
                continue;
            }

            $retryAfter = $headers[$header . '-Decay'];
        }

        if ($retryAfter > 0) {
            throw new LimitReachedException($retryAfter);
        }
    }
}
