<?php

namespace PlentymarketsAdapter\ReadApi\Comment;

use PlentymarketsAdapter\ReadApi\ApiAbstract;

/**
 * Class Comment
 */
class Comment extends ApiAbstract
{
    /**
     * @param int $id
     *
     * @return array
     */
    public function find($id)
    {
        return $this->client->request('GET', 'comments/' . $id);
    }

    /**
     * @param array $criteria
     *
     * @return array
     */
    public function findAll(array $criteria = [])
    {
        return $this->client->request('GET', $this->getUrl($criteria), $this->getParams($criteria));
    }

    /**
     * @param array $criteria
     *
     * @return array
     */
    public function findBy(array $criteria = [])
    {
        return $this->client->request('GET', $this->getUrl($criteria), $this->getParams($criteria));
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
     * @param array $criteria
     *
     * @return string
     */
    private function getUrl(array $criteria)
    {
        $url = 'comments';

        if (isset($criteria['referenceType'])) {
            $url .= '/' . $criteria['referenceType'];
        }

        if (isset($criteria['referenceValue'])) {
            $url .= '/' . $criteria['referenceValue'];
        }

        return $url;
    }

    /**
     * @param array $criteria
     *
     * @return array
     */
    private function getParams(array $criteria)
    {
        $result = [];
        foreach ($criteria as $key => $criterion) {
            if ($key === 'referenceType' || $key === 'referenceValue') {
                continue;
            }

            $result[$key] = $criterion;
        }

        return $result;
    }
}
