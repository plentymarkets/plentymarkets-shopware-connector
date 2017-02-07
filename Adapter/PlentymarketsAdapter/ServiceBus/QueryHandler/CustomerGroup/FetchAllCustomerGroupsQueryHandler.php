<?php

namespace PlentymarketsAdapter\ServiceBus\QueryHandler\CustomerGroup;

use PlentyConnector\Connector\ServiceBus\Query\CustomerGroup\FetchAllCustomerGroupsQuery;
use PlentyConnector\Connector\ServiceBus\Query\QueryInterface;
use PlentyConnector\Connector\ServiceBus\QueryHandler\QueryHandlerInterface;
use PlentymarketsAdapter\Client\ClientInterface;
use PlentymarketsAdapter\PlentymarketsAdapter;
use PlentymarketsAdapter\ResponseParser\CustomerGroup\CustomerGroupResponseParserInterface;

/**
 * Class FetchAllCustomerGroupsQueryHandler
 */
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

    /**
     * FetchAllCustomerGroupsQueryHandler constructor.
     *
     * @param ClientInterface                      $client
     * @param CustomerGroupResponseParserInterface $responseParser
     */
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
    public function supports(QueryInterface $query)
    {
        return $query instanceof FetchAllCustomerGroupsQuery &&
            $query->getAdapterName() === PlentymarketsAdapter::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(QueryInterface $query)
    {
        $customerGroups = [];
        $response = $this->client->request('GET', 'accounts/contacts/classes');

        foreach ($response as $id => $name) {
            $customerGroups[] = $this->responseParser->parse(['id' => $id, 'name' => $name]);
        }

        return array_filter($customerGroups);
    }
}
