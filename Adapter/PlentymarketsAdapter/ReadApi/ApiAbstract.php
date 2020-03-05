<?php

namespace PlentymarketsAdapter\ReadApi;

use PlentymarketsAdapter\Client\ClientInterface;

abstract class ApiAbstract
{
    /**
     * @var ClientInterface
     */
    protected $client;

    public function __construct(ClientInterface $client)
    {
        $this->client = $client;
    }

    /**
     * Getter for Client
     */
    public function getClient(): ClientInterface
    {
        return $this->client;
    }

    public function setClient(ClientInterface $client)
    {
        $this->client = $client;
    }
}
