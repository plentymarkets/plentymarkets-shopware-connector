<?php

namespace SystemConnector\ServiceBus\QueryFactory;

use Assert\Assertion;
use SystemConnector\ServiceBus\Query\FetchTransferObjectQuery;
use SystemConnector\ServiceBus\QueryFactory\Exception\MissingQueryException;
use SystemConnector\ServiceBus\QueryType;

class QueryFactory implements QueryFactoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function create($adapterName, $objectType, $queryType, $payload = null)
    {
        Assertion::string($adapterName);
        Assertion::string($objectType);
        Assertion::inArray($queryType, QueryType::getAllTypes());

        if ($queryType === QueryType::ONE) {
            Assertion::uuid($payload);
        }

        $query = null;

        switch ($queryType) {
            case QueryType::ONE:
                $query = new FetchTransferObjectQuery($adapterName, $objectType, $queryType, $payload);

                break;
            case QueryType::CHANGED:
                $query = new FetchTransferObjectQuery($adapterName, $objectType, $queryType);

                break;
            case QueryType::ALL:
                $query = new FetchTransferObjectQuery($adapterName, $objectType, $queryType);

                break;
        }

        if (null === $query) {
            throw MissingQueryException::fromObjectData($objectType, $queryType);
        }

        return $query;
    }
}
