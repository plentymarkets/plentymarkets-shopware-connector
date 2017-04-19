<?php

namespace ShopwareAdapter\ServiceBus\QueryHandler\Order;

use PlentyConnector\Connector\ServiceBus\Query\Order\FetchAllOrdersQuery;
use PlentyConnector\Connector\ServiceBus\Query\QueryInterface;
use PlentyConnector\Connector\ServiceBus\QueryHandler\QueryHandlerInterface;
use Shopware\Components\Api\Resource\Order as OrderResource;
use Shopware\Models\Order\Status;
use ShopwareAdapter\ResponseParser\Order\OrderResponseParserInterface;
use ShopwareAdapter\ShopwareAdapter;

/**
 * Class FetchAllOrdersQueryHandler
 */
class FetchAllOrdersQueryHandler implements QueryHandlerInterface
{
    /**
     * @var OrderResponseParserInterface
     */
    private $responseParser;

    /**
     * @var OrderResource
     */
    private $orderResource;

    /**
     * FetchAllOrdersQueryHandler constructor.
     *
     * @param OrderResponseParserInterface $responseParser
     * @param OrderResource               $orderResource
     */
    public function __construct(
        OrderResponseParserInterface $responseParser,
        OrderResource $orderResource
    ) {
        $this->responseParser = $responseParser;
        $this->orderResource = $orderResource;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(QueryInterface $query)
    {
        return $query instanceof FetchAllOrdersQuery &&
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
