<?php

namespace PlentymarketsAdapter\ReadApi;

class Availability extends ApiAbstract
{
    /**
     * @return array
     */
    public function findAll()
    {
        return $this->client->request('GET', 'availabilities');
    }
}
