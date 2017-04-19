<?php

namespace ShopwareAdapter\ServiceBus\QueryHandler\Payment;

use PlentyConnector\Connector\ServiceBus\Query\Payment\FetchChangedPaymentsQuery;
use PlentyConnector\Connector\ServiceBus\Query\QueryInterface;
use PlentyConnector\Connector\ServiceBus\QueryHandler\QueryHandlerInterface;
use ShopwareAdapter\ResponseParser\Payment\PaymentResponseParserInterface;
use Shopware\Components\Api\Resource\Order as OrderResource;
use Shopware\Models\Order\Status;
use ShopwareAdapter\ShopwareAdapter;

/**
 * Class FetchChangedPaymentsQueryHandler
 */
class FetchChangedPaymentsQueryHandler implements QueryHandlerInterface
{
    /**
     * @var PaymentResponseParserInterface
     */
    private $responseParser;

    /**
     * @var OrderResource
     */
    private $orderResource;

    /**
     * FetchAllPaymentsQueryHandler constructor.
     *
     * @param PaymentResponseParserInterface $responseParser
     * @param OrderResource $orderResource
     */
    public function __construct(PaymentResponseParserInterface $responseParser, OrderResource $orderResource)
    {
        $this->responseParser = $responseParser;
        $this->orderResource = $orderResource;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(QueryInterface $query)
    {
        return $query instanceof FetchChangedPaymentsQuery &&
            $query->getAdapterName() === ShopwareAdapter::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(QueryInterface $query)
    {
        $filter = [
            [
                'property' => 'status',
                'expression' => '=',
                'value' => Status::ORDER_STATE_OPEN,
            ],
        ];

        $orders = $this->orderResource->getList(0, null, $filter);

        foreach ($orders['data'] as $order) {
            $order = $this->orderResource->getOne($order['id']);

            $parsedElements = array_filter($this->responseParser->parse($order));

            foreach ($parsedElements as $parsedElement) {
                yield $parsedElement;
            }
        }
    }
}
