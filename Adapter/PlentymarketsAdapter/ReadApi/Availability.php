<?php

namespace PlentymarketsAdapter\ReadApi;

class Availability extends ApiAbstract
{
    public function findAll(): array
    {
        return $this->client->request('GET', 'availabilities');
    }
}
