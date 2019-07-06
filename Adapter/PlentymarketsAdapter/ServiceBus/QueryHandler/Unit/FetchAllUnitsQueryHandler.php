<?php

namespace PlentymarketsAdapter\ServiceBus\QueryHandler\Unit;

use PlentymarketsAdapter\PlentymarketsAdapter;
use PlentymarketsAdapter\ReadApi\Item\Unit as UnitApi;
use PlentymarketsAdapter\ResponseParser\Unit\UnitResponseParserInterface;
use SystemConnector\ServiceBus\Query\FetchTransferObjectQuery;
use SystemConnector\ServiceBus\Query\QueryInterface;
use SystemConnector\ServiceBus\QueryHandler\QueryHandlerInterface;
use SystemConnector\ServiceBus\QueryType;
use SystemConnector\TransferObject\Unit\Unit;

class FetchAllUnitsQueryHandler implements QueryHandlerInterface
{
    /**
     * @var UnitApi
     */
    private $unitApi;

    /**
     * @var UnitResponseParserInterface
     */
    private $responseParser;

    public function __construct(UnitApi $unitApi, UnitResponseParserInterface $responseParser)
    {
        $this->unitApi = $unitApi;
        $this->responseParser = $responseParser;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(QueryInterface $query): bool
    {
        return $query instanceof FetchTransferObjectQuery &&
            $query->getAdapterName() === PlentymarketsAdapter::NAME &&
            $query->getObjectType() === Unit::TYPE &&
            $query->getQueryType() === QueryType::ALL;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(QueryInterface $query)
    {
        $elements = $this->unitApi->findAll();

        foreach ($elements as $element) {
            $result = $this->responseParser->parse($element);

            if (null === $result) {
                continue;
            }

            yield $result;
        }
    }
}
