<?php

namespace PlentymarketsAdapter\ServiceBus\QueryHandler\CustomerGroup;

use PlentymarketsAdapter\Client\ClientInterface;
use PlentymarketsAdapter\PlentymarketsAdapter;
use PlentymarketsAdapter\ResponseParser\CustomerGroup\CustomerGroupResponseParserInterface;
use SystemConnector\ServiceBus\Query\FetchTransferObjectQuery;
use SystemConnector\ServiceBus\Query\QueryInterface;
use SystemConnector\ServiceBus\QueryHandler\QueryHandlerInterface;
use SystemConnector\ServiceBus\QueryType;
use SystemConnector\TransferObject\CustomerGroup\CustomerGroup;

class FetchAllCustomerGroupsQueryHandler implements QueryHandlerInterface
{
    /**
     * @var ClientInterface
     */
    private $client;

    /**
     * @var CustomerGroupResponseParserInterface
     */
    private $responseParser;

    public function __construct(
        ClientInterface $client,
        CustomerGroupResponseParserInterface $responseParser
    ) {
        $this->client = $client;
        $this->responseParser = $responseParser;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(QueryInterface $query): bool
    {
        return $query instanceof FetchTransferObjectQuery &&
            $query->getAdapterName() === PlentymarketsAdapter::NAME &&
            $query->getObjectType() === CustomerGroup::TYPE &&
            $query->getQueryType() === QueryType::ALL;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(QueryInterface $query)
    {
        $elements = $this->client->request('GET', 'accounts/contacts/classes');

        foreach ($elements as $key => $element) {
            $result = $this->responseParser->parse(['id' => $key, 'name' => $element]);

            if (null === $result) {
                continue;
            }

            yield $result;
        }
    }
}
