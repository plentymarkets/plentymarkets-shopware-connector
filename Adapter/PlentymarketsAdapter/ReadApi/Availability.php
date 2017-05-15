<?php

namespace PlentymarketsAdapter\ReadApi;

/**
 * Class Availability
 */
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
