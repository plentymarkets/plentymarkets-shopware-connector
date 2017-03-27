<?php

namespace ShopwareAdapter\ServiceBus\QueryHandler\Order;

use PlentyConnector\Connector\ServiceBus\Query\Order\FetchChangedOrdersQuery;
use PlentyConnector\Connector\ServiceBus\Query\QueryInterface;
use PlentyConnector\Connector\ServiceBus\QueryHandler\QueryHandlerInterface;
use Shopware\Components\Api\Resource;
use ShopwareAdapter\ResponseParser\Order\OrderResponseParserInterface;
use ShopwareAdapter\ServiceBus\ChangedDateTimeTrait;
use ShopwareAdapter\ShopwareAdapter;

/**
 * Class FetchChangedOrdersQueryHandler.
 */
class FetchChangedOrdersQueryHandler implements QueryHandlerInterface
{
    use ChangedDateTimeTrait;

    /**
     * @var OrderResponseParserInterface
     */
    private $responseParser;

    /**
     * @var Resource\Order
     */
    private $orderResource;

    /**
     * FetchChangedOrdersQueryHandler constructor.
     *
     * @param OrderResponseParserInterface $responseParser
     * @param Resource\Order               $orderResource
     */
    public function __construct(
        OrderResponseParserInterface $responseParser,
        Resource\Order $orderResource
    ) {
        $this->responseParser = $responseParser;
        $this->orderResource = $orderResource;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(QueryInterface $event)
    {
        return $event instanceof FetchChangedOrdersQuery &&
            $event->getAdapterName() === ShopwareAdapter::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(QueryInterface $event)
    {
        $filter = [
            [
                'property'   => 'status',
                'expression' => '!=',
                'value'      => -1,
            ],
        ];

        $orders = $this->orderResource->getList(0, null, $filter);

        $result = array_map(function ($order) {
            return $this->responseParser->parse($this->orderResource->getOne($order['id']));
        }, $orders['data']);

        return array_filter($result);
    }
}
