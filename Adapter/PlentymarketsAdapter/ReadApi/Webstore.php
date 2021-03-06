<?php

namespace PlentymarketsAdapter\ReadApi;

class Webstore extends ApiAbstract
{
    /**
     * @return array
     */
    public function findAll(): array
    {
        $webstores = $this->client->request('GET', 'webstores');

        $result = [];
        foreach ($webstores as $webstore) {
            $result[$webstore['id']] = $webstore;
        }

        return $result;
    }
}
