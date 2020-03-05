<?php

namespace PlentymarketsAdapter\ReadApi\Customer;

use PlentymarketsAdapter\ReadApi\ApiAbstract;

class Customer extends ApiAbstract
{
    /**
     * @param int $id
     */
    public function find($id): array
    {
        return $this->client->request('GET', 'accounts/contacts/' . $id);
    }

    public function findAll(array $criteria = []): array
    {
        return $this->client->request('GET', 'accounts/contacts', $criteria);
    }

    public function findBy(array $criteria = []): array
    {
        return $this->client->request('GET', 'accounts/contacts', $criteria);
    }

    public function findOneBy(array $criteria = []): array
    {
        $result = $this->findBy($criteria);

        if (!empty($result)) {
            $result = array_shift($result);
        }

        return $result;
    }
}
