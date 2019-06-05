<?php

namespace PlentymarketsAdapter\ReadApi\Customer;

use PlentymarketsAdapter\ReadApi\ApiAbstract;

class Customer extends ApiAbstract
{
    /**
     * @param int $id
     *
     * @return array
     */
    public function find($id): array
    {
        return $this->client->request('GET', 'accounts/contacts/' . $id);
    }

    /**
     * @param array $criteria
     *
     * @return array
     */
    public function findAll(array $criteria = []): array
    {
        return $this->client->request('GET', 'accounts/contacts', $criteria);
    }

    /**
     * @param array $criteria
     *
     * @return array
     */
    public function findBy(array $criteria = []): array
    {
        return $this->client->request('GET', 'accounts/contacts', $criteria);
    }

    /**
     * @param array $criteria
     *
     * @return array
     */
    public function findOneBy(array $criteria = []): array
    {
        $result = $this->findBy($criteria);

        if (!empty($result)) {
            $result = array_shift($result);
        }

        return $result;
    }
}
