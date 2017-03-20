<?php

namespace PlentymarketsAdapter\ReadApi;

/**
 * Class Webstore
 */
class Webstore extends ApiAbstract
{
    /**
     * @param null $productId
     *
     * @return array
     */
    public function findAll()
    {
        return $this->client->request('GET', 'webstores');
    }
}
