<?php

namespace PlentymarketsAdapter\ReadApi\Account;

use PlentymarketsAdapter\ReadApi\ApiAbstract;

class ContactClass extends ApiAbstract
{
    /**
     * @return array
     */
    public function findAll(): array
    {
        return $this->client->request('GET', 'accounts/contacts/classes');
    }
}
