<?php

namespace PlentymarketsAdapter\ReadApi;

use PlentymarketsAdapter\Client\ClientInterface;

/**
 * Class ApiAbstract
 */
abstract class ApiAbstract
{
    /**
     * @var ClientInterface
     */
    protected $client;

    /**
     * ApiAbstract constructor.
     *
     * @param ClientInterface $client
     */
    public function __construct(ClientInterface $client)
    {
        $this->client = $client;
    }

    /**
     * Getter for Client
     *
     * @return ClientInterface
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * @param ClientInterface $client
     */
    public function setClient(ClientInterface $client)
    {
        $this->client = $client;
    }
}
