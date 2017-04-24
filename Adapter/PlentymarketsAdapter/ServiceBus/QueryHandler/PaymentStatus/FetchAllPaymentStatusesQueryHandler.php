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
     * FetchAllPaymentStatusesQueryHandler constructor.     *
     *
     * @param ClientInterface                      $client
     * @param PaymentStatusResponseParserInterface $responseParser
     */
    public function __construct(
        ClientInterface $client,
        PaymentStatusResponseParserInterface $responseParser
    ) {
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
        $paymentStatuses = array_map(function ($orderStatus) {
            return $this->responseParser->parse($orderStatus);
        }, $this->client->request('GET', 'orders/statuses', ['with' => 'names']));

        return array_filter($paymentStatuses);
    }
}
