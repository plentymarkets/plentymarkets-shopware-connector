<?php

namespace PlentymarketsAdapter\ServiceBus\QueryHandler\CustomerGroup;

use PlentyConnector\Connector\ServiceBus\Query\FetchTransferObjectQuery;
use PlentyConnector\Connector\ServiceBus\Query\QueryInterface;
use PlentyConnector\Connector\ServiceBus\QueryHandler\QueryHandlerInterface;
use PlentyConnector\Connector\ServiceBus\QueryType;
use PlentyConnector\Connector\TransferObject\CustomerGroup\CustomerGroup;
use PlentymarketsAdapter\Client\ClientInterface;
use PlentymarketsAdapter\PlentymarketsAdapter;
use PlentymarketsAdapter\ResponseParser\CustomerGroup\CustomerGroupResponseParserInterface;

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
