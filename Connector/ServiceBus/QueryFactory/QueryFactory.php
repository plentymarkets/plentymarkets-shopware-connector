<?php

namespace PlentyConnector\Connector\ServiceBus\QueryFactory;

use Assert\Assertion;
use PlentyConnector\Connector\ServiceBus\QueryFactory\Exception\MissingQueryException;
use PlentyConnector\Connector\ServiceBus\QueryFactory\Exception\MissingQueryGeneratorException;
use PlentyConnector\Connector\ServiceBus\QueryGenerator\QueryGeneratorInterface;
use PlentyConnector\Connector\ServiceBus\QueryType;

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
