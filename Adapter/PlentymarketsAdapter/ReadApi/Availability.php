<?php

namespace PlentymarketsAdapter\ReadApi;

class Availability extends ApiAbstract
{
    /**
     * @return array
     */
    public function findAll(): array
    {
        return $this->client->request('GET', 'availabilities');
    }
}
