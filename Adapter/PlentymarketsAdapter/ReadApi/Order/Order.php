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
     * @var string
     */
    private $includes = 'addresses,relation';

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
     * @return array
     */
    public function findAll(array $criteria = [])
    {
        $criteria = array_merge($criteria, [
            'with' => $this->includes,
        ]);

        return iterator_to_array($this->client->getIterator('orders', $criteria));
    }

    /**
     * @param array $criteria
     *
     * @return array
     */
    public function findBy(array $criteria = [])
    {
        $criteria = array_merge($criteria, [
            'with' => $this->includes,
        ]);

        return iterator_to_array($this->client->getIterator('orders', $criteria));
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

    /**
     * @param $params
     *
     * @return array
     */
    public function create($params)
    {
        throw new \Exception('not implemented yet');
    }

    /**
     * @param $id
     * @param $params
     *
     * @return array
     */
    public function update($id, $params)
    {
        throw new \Exception('not implemented yet');
    }

    /**
     * @param $id
     *
     * @return bool
     */
    public function delete($id)
    {
        throw new \Exception('not implemented yet');
    }
}
