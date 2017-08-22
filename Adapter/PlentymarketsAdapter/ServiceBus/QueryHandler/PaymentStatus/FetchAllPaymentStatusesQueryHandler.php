<?php

namespace PlentymarketsAdapter\ServiceBus\QueryHandler\PaymentStatus;

use PlentyConnector\Connector\ServiceBus\Query\PaymentStatus\FetchAllPaymentStatusesQuery;
use PlentyConnector\Connector\ServiceBus\Query\QueryInterface;
use PlentyConnector\Connector\ServiceBus\QueryHandler\QueryHandlerInterface;
use PlentymarketsAdapter\Client\ClientInterface;
use PlentymarketsAdapter\PlentymarketsAdapter;
use PlentymarketsAdapter\ResponseParser\PaymentStatus\PaymentStatusResponseParserInterface;

/**
 * Class FetchAllPaymentStatusesQueryHandler
 */
class FetchAllPaymentStatusesQueryHandler implements QueryHandlerInterface
{
    /**
     * @var ClientInterface
     */
    private $client;

    /**
     * @var PaymentStatusResponseParserInterface
     */
    private $responseParser;

    /**
     * FetchAllPaymentStatusesQueryHandler constructor.
     *
     * @param ClientInterface                      $client
     * @param PaymentStatusResponseParserInterface $responseParser
     */
    public function __construct(
        ClientInterface $client,
        PaymentStatusResponseParserInterface $responseParser
    ) {
        $this->client = $client;
        $this->responseParser = $responseParser;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(QueryInterface $query)
    {
        return $query instanceof FetchAllPaymentStatusesQuery &&
            $query->getAdapterName() === PlentymarketsAdapter::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(QueryInterface $query)
    {
        $status = $this->client->getIterator('orders/statuses', ['with' => 'names']);

        $result = [];
        foreach ($status as $orderStatus) {
            $result[] = $this->responseParser->parse($orderStatus);
        }

        return array_filter($result);
    }
}
