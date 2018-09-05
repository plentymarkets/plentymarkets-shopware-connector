<?php

namespace PlentymarketsAdapter\ReadApi\Account;

use PlentymarketsAdapter\ReadApi\ApiAbstract;

class ContactClass extends ApiAbstract
{
    /**
     * @return array
     */
    public function findAll()
    {
        return $this->client->request('GET', 'accounts/contacts/classes');
    }
}
