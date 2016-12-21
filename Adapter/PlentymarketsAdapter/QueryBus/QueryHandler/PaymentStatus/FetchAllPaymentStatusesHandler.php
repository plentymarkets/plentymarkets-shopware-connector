<?php

namespace PlentymarketsAdapter\QueryBus\QueryHandler\PaymentStatus;

use PlentyConnector\Connector\QueryBus\Query\PaymentStatus\FetchAllPaymentStatusesQuery;
use PlentyConnector\Connector\QueryBus\Query\QueryInterface;
use PlentyConnector\Connector\QueryBus\QueryHandler\QueryHandlerInterface;
use PlentymarketsAdapter\Client\ClientInterface;
use PlentymarketsAdapter\PlentymarketsAdapter;
use PlentymarketsAdapter\ResponseParser\ResponseParserInterface;

/**
 * Class FetchAllPaymentStatusesHandler
 */
class FetchAllPaymentStatusesHandler implements QueryHandlerInterface
{
    /**
     * @var ClientInterface
     */
    private $client;

    /**
     * @var ResponseParserInterface
     */
    private $responseParser;

    /**
     * FetchAllPaymentStatusesHandler constructor.
     *
     * @param ClientInterface $client
     * @param ResponseParserInterface $responseParser
     */
    public function __construct(
        ClientInterface $client,
        ResponseParserInterface $responseParser
    ) {
        $this->client = $client;
        $this->responseParser = $responseParser;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(QueryInterface $event)
    {
        return $event instanceof FetchAllPaymentStatusesQuery &&
            $event->getAdapterName() === PlentymarketsAdapter::getName();
    }

    /**
     * @return array
     */
    private function getPaymentStatuses()
    {
        return [
            [
                'id' => 1,
                'name' => 'Awaiting approval',
            ],
            [
                'id' => 2,
                'name' => 'Approved',
            ],
            [
                'id' => 3,
                'name' => 'Captured',
            ],
            [
                'id' => 4,
                'name' => 'Partially captured',
            ],
            [
                'id' => 5,
                'name' => 'Cancelled',
            ],
            [
                'id' => 6,
                'name' => 'Refused',
            ],
            [
                'id' => 7,
                'name' => 'Awaiting renewal',
            ],
            [
                'id' => 8,
                'name' => 'Expired',
            ],
            [
                'id' => 9,
                'name' => 'Refunded',
            ],
            [
                'id' => 10,
                'name' => 'Partially refunded',
            ]
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function handle(QueryInterface $event)
    {
        $paymentStatuses = array_map(function ($status) {
            return $this->responseParser->parse($status);
        }, $this->getPaymentStatuses());

        return array_filter($paymentStatuses);
    }
}
