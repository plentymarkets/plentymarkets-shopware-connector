<?php

namespace PlentymarketsAdapter\ServiceBus\QueryHandler\PaymentMethod;

use PlentymarketsAdapter\Client\ClientInterface;
use PlentymarketsAdapter\PlentymarketsAdapter;
use PlentymarketsAdapter\ResponseParser\PaymentMethod\PaymentMethodResponseParserInterface;
use SystemConnector\ServiceBus\Query\FetchTransferObjectQuery;
use SystemConnector\ServiceBus\Query\QueryInterface;
use SystemConnector\ServiceBus\QueryHandler\QueryHandlerInterface;
use SystemConnector\ServiceBus\QueryType;
use SystemConnector\TransferObject\PaymentMethod\PaymentMethod;

class FetchAllPaymentMethodsQueryHandler implements QueryHandlerInterface
{
    /**
     * @var ClientInterface
     */
    private $client;

    /**
     * @var PaymentMethodResponseParserInterface
     */
    private $responseParser;

    public function __construct(
        ClientInterface $client,
        PaymentMethodResponseParserInterface $responseParser
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
            $query->getObjectType() === PaymentMethod::TYPE &&
            $query->getQueryType() === QueryType::ALL;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(QueryInterface $query)
    {
        $elements = $this->client->request('GET', 'payments/methods');

        foreach ($elements as $element) {
            $result = $this->responseParser->parse($element);

            if (null === $result) {
                continue;
            }

            yield $result;
        }
    }
}
