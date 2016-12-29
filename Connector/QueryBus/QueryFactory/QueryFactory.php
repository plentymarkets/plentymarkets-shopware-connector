<?php

namespace PlentyConnector\Connector\QueryBus\QueryFactory;

use Assert\Assertion;
use PlentyConnector\Connector\QueryBus\QueryGenerator\QueryGeneratorInterface;
use PlentyConnector\Connector\QueryBus\QueryType;

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
    public function create($adapterName, $objectType, $queryType, $identifier = null)
    {
        Assertion::string($adapterName);
        Assertion::string($queryType);
        Assertion::string($objectType);
        Assertion::inArray($queryType, QueryType::getAllTypes());

        if ($queryType === QueryType::ONE) {
            Assertion::notNull($identifier);
            Assertion::uuid($identifier);
        }

        /**
         * @var QueryGeneratorInterface[] $generators
         */
        $generators = array_filter($this->generators,
            function (QueryGeneratorInterface $generator) use ($objectType) {
                return $generator->supports($objectType);
            }
        );

        $generator = array_shift($generators);

        if (null === $generator) {
            return null;
        }

        switch ($queryType) {
            case QueryType::ONE:
                return $generator->generateFetchQuery($adapterName, $identifier);
            case QueryType::CHANGED:
                return $generator->generateFetchChangedQuery($adapterName);
            case QueryType::ALL:
                return $generator->generateFetchAllQuery($adapterName);
        }
    }
}
