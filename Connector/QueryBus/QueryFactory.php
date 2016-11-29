<?php

namespace PlentyConnector\Connector\QueryBus;

use Assert\Assertion;
use PlentyConnector\Connector\QueryBus\QueryGenerator\QueryGeneratorInterface;
use PlentyConnector\Connector\QueryBus\Query\QueryInterface;
use PlentyConnector\Connector\TransferObject\TransferObjectType;

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
     * @param QueryGeneratorInterface $generator
     */
    public function addGenerator(QueryGeneratorInterface $generator)
    {
        $this->generators = $generator;
    }

    /**
     * @param string $adapterName
     * @param $transferObjectName
     * @param $identifier
     *
     * @return QueryInterface[]
     */
    public function create($adapterName, $transferObjectName, $identifier, $type)
    {
        // TODO: check $adapterName
        Assertion::inArray($transferObjectName, TransferObjectType::getAllTypes());
        Assertion::uuid($identifier);

        $generators = array_filter($this->generators, function(QueryGeneratorInterface $generator) use ($transferObjectName) {
            return $generator->supports($transferObjectName);
        });

        if ($type == "ALL") {
            $query = $generators->generateFetchAllQuery($adapterName);
        }

        return $query;
    }
}
