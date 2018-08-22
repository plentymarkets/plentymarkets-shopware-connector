<?php

namespace PlentymarketsAdapter\ReadApi\Order;

use PlentymarketsAdapter\Client\Iterator\Iterator;
use PlentymarketsAdapter\ReadApi\ApiAbstract;

/**
 * Class Order
 */
class Order extends ApiAbstract
{
    /**
     * @var array
     */
    private $includes = ['addresses', 'relations', 'addresses', 'comments', ];

    /**
     * @param int $id
     *
     * @return array
     */
    public function find($id)
    {
        $criteria = [
            'with' => $this->includes,
        ];

        return $this->client->request('GET', 'orders/' . $id, $criteria);
    }

    /**
     * @param array $criteria
     *
     * @return Iterator
     */
    public function findAll(array $criteria = [])
    {
        $criteria = array_merge($criteria, [
            'with' => $this->includes,
        ]);

        return $this->client->getIterator('orders', $criteria);
    }

    /**
     * @param array $criteria
     *
     * @return Iterator
     */
    public function findBy(array $criteria = [])
    {
        $criteria = array_merge($criteria, [
            'with' => $this->includes,
        ]);

        return $this->client->getIterator('orders', $criteria);
    }

    /**
     * @param array $criteria
     *
     * @return array
     */
    public function findOneBy(array $criteria = [])
    {
        $result = iterator_to_array($this->findBy($criteria));

        if (!empty($result)) {
            $result = array_shift($result);
        }

        return $result;
    }
}
