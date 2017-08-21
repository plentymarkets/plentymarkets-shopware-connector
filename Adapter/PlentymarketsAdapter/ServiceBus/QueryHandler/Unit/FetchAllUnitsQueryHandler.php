<?php

namespace PlentymarketsAdapter\ServiceBus\QueryHandler\Unit;

use PlentyConnector\Connector\ServiceBus\Query\QueryInterface;
use PlentyConnector\Connector\ServiceBus\Query\Unit\FetchAllUnitsQuery;
use PlentyConnector\Connector\ServiceBus\QueryHandler\QueryHandlerInterface;
use PlentymarketsAdapter\Client\ClientInterface;
use PlentymarketsAdapter\PlentymarketsAdapter;
use PlentymarketsAdapter\ResponseParser\Unit\UnitResponseParserInterface;

/**
 * Class FetchAllUnitsQueryHandler
 */
class FetchAllUnitsQueryHandler implements QueryHandlerInterface
{
    /**
     * @var ClientInterface
     */
    private $client;

    /**
     * @var UnitResponseParserInterface
     */
    private $responseParser;

    /**
     * FetchAllUnitsQueryHandler constructor.
     *
     * @param ClientInterface             $client
     * @param UnitResponseParserInterface $responseParser
     */
    public function __construct(
        ClientInterface $client,
        UnitResponseParserInterface $responseParser
    ) {
        $this->client = $client;
        $this->responseParser = $responseParser;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(QueryInterface $query)
    {
        return $query instanceof FetchAllUnitsQuery &&
            $query->getAdapterName() === PlentymarketsAdapter::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(QueryInterface $query)
    {
        $units = $this->client->getIterator('items/units');

        $result = [];
        foreach ($units as $unit) {
            $result[] = $this->responseParser->parse($unit);
        }

        return array_filter($result);
    }
}
