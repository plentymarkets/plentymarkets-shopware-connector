<?php

namespace PlentymarketsAdapter\ReadApi\Payment;

use PlentymarketsAdapter\Client\Iterator\Iterator;
use PlentymarketsAdapter\ReadApi\ApiAbstract;

class Payment extends ApiAbstract
{
    /**
     * @param int $id
     *
     * @return array
     */
    public function find($id)
    {
        return $this->client->request('GET', 'payments/' . $id);
    }

    /**
     * @param array $criteria
     *
     * @return Iterator
     */
    public function findAll(array $criteria = [])
    {
        return $this->client->getIterator('payments', $criteria);
    }

    /**
     * @param array $criteria
     *
     * @return Iterator
     */
    public function findBy(array $criteria = [])
    {
        return $this->client->getIterator('payments', $criteria);
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
