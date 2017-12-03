<?php

namespace PlentymarketsAdapter\ReadApi\Address;

use PlentymarketsAdapter\ReadApi\ApiAbstract;

/**
 * Class Address
 */
class Address extends ApiAbstract
{
    /**
     * @param int $id
     *
     * @return array
     */
    public function find($id)
    {
        return $this->client->request('GET', 'accounts/addresses/' . $id);
    }

    /**
     * @param array $criteria
     *
     * @return array
     */
    public function findAll(array $criteria = [])
    {
        return $this->client->request('GET', 'accounts/addresses', $criteria);
    }

    /**
     * @param array $criteria
     *
     * @return array
     */
    public function findBy(array $criteria = [])
    {
        return $this->client->request('GET', 'accounts/addresses', $criteria);
    }

    /**
     * @param array $criteria
     *
     * @return array
     */
    public function findOneBy(array $criteria = [])
    {
        $result = $this->findBy($criteria);

        if (!empty($result)) {
            $result = array_shift($result);
        }

        return $result;
    }
}
