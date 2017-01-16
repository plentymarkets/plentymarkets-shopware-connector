<?php

namespace PlentyConnector\Connector\QueryBus\QueryFactory;

use Assert\Assertion;
use PlentyConnector\Connector\QueryBus\QueryFactory\Exception\MissingQueryException;
use PlentyConnector\Connector\QueryBus\QueryFactory\Exception\MissingQueryGeneratorException;
use PlentyConnector\Connector\QueryBus\QueryGenerator\QueryGeneratorInterface;
use PlentyConnector\Connector\QueryBus\QueryType;
use PlentyConnector\Connector\TransferObject\TransferObjectInterface;

/**
 * Class QueryFactory.
 */
class QueryFactory implements QueryFactoryInterface
{
    /**
     * @var QueryGeneratorInterface[]
     */
    private $generators = [];

    /**
     * {@inheritdoc}
     */
    public function addGenerator(QueryGeneratorInterface $generator)
    {
        $this->generators[] = $generator;
    }

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

        /**
         * @var QueryGeneratorInterface[] $generators
         */
        $generators = array_filter($this->generators, function (QueryGeneratorInterface $generator) use ($objectType) {
            return $generator->supports($objectType);
        });

        $generator = array_shift($generators);

        if (null === $generator) {
            throw MissingQueryGeneratorException::fromObjectData($objectType, $queryType);
        }

        $query = null;

        switch ($queryType) {
            case QueryType::ONE:
                $query = $generator->generateFetchQuery($adapterName, $payload);
                break;
            case QueryType::CHANGED:
                $query = $generator->generateFetchChangedQuery($adapterName);
                break;
            case QueryType::ALL:
                $query = $generator->generateFetchAllQuery($adapterName);
                break;
        }

        if (null === $query) {
            throw MissingQueryException::fromObjectData($objectType, $queryType);
        }

        return $query;
    }
}
