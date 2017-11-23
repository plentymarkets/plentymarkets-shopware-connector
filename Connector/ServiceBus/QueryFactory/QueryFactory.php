<?php

namespace PlentyConnector\Connector\ServiceBus\QueryFactory;

use Assert\Assertion;
use PlentyConnector\Connector\ServiceBus\Query\FetchTransferObjectQuery;
use PlentyConnector\Connector\ServiceBus\QueryFactory\Exception\MissingQueryException;
use PlentyConnector\Connector\ServiceBus\QueryType;

/**
 * Class QueryFactory.
 */
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
