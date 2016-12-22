<?php

namespace PlentymarketsAdapter\QueryBus\QueryHandler\PaymentStatus;

use PlentyConnector\Connector\QueryBus\Query\PaymentStatus\FetchAllPaymentStatusesQuery;
use PlentyConnector\Connector\QueryBus\Query\QueryInterface;
use PlentyConnector\Connector\QueryBus\QueryHandler\QueryHandlerInterface;
use PlentymarketsAdapter\PlentymarketsAdapter;
use PlentymarketsAdapter\ResponseParser\ResponseParserInterface;

/**
 * Class FetchAllPaymentStatusesHandler
 */
class FetchAllPaymentStatusesHandler implements QueryHandlerInterface
{
    /**
     * @var ResponseParserInterface
     */
    private $responseParser;

    /**
     * FetchAllPaymentStatusesHandler constructor.
     *
     * @param ResponseParserInterface $responseParser
     */
    public function __construct(
        ResponseParserInterface $responseParser
    ) {
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
     * {@inheritdoc}
     */
    public function handle(QueryInterface $event)
    {
        $paymentStatuses = array_map(function ($status) {
            return $this->responseParser->parse($status);
        }, $this->getPaymentStatuses());

        return $paymentStatuses;
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
}
