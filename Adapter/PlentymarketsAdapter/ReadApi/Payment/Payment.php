<?php

namespace PlentymarketsAdapter\ReadApi\Payment;

use PlentymarketsAdapter\Client\Iterator\Iterator;
use PlentymarketsAdapter\ReadApi\ApiAbstract;

class Payment extends ApiAbstract
{
    /**
     * @param int $id
     */
    public function find($id): array
    {
        return $this->client->request('GET', 'payments/' . $id);
    }

    public function findAll(array $criteria = []): Iterator
    {
        return $this->client->getIterator('payments', $criteria);
    }

    public function findBy(array $criteria = []): Iterator
    {
        return $this->client->getIterator('payments', $criteria);
    }

    public function findOneBy(array $criteria = []): array
    {
        $result = iterator_to_array($this->findBy($criteria));

        if (null !== $result) {
            $result = array_shift($result);
        }

        return $result;
    }
}
