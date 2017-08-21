<?php

namespace PlentymarketsAdapter\ServiceBus\QueryHandler\PaymentMethod;

use PlentyConnector\Connector\ServiceBus\Query\PaymentMethod\FetchAllPaymentMethodsQuery;
use PlentyConnector\Connector\ServiceBus\Query\QueryInterface;
use PlentyConnector\Connector\ServiceBus\QueryHandler\QueryHandlerInterface;
use PlentymarketsAdapter\Client\ClientInterface;
use PlentymarketsAdapter\PlentymarketsAdapter;
use PlentymarketsAdapter\ResponseParser\PaymentMethod\PaymentMethodResponseParserInterface;

/**
 * Class FetchAllPaymentMethodsQueryHandler
 */
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

    /**
     * FetchAllPaymentMethodsQueryHandler constructor.
     *
     * @param ClientInterface                      $client
     * @param PaymentMethodResponseParserInterface $responseParser
     */
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
    public function supports(QueryInterface $query)
    {
        return $query instanceof FetchAllPaymentMethodsQuery &&
            $query->getAdapterName() === PlentymarketsAdapter::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(QueryInterface $query)
    {
        $paymentMethods = $this->client->getIterator('payments/methods');

        $result = [];
        foreach ($paymentMethods as $paymentMethod) {
            $result[] = $this->responseParser->parse($paymentMethod);
        }

        return array_filter($result);
    }
}
