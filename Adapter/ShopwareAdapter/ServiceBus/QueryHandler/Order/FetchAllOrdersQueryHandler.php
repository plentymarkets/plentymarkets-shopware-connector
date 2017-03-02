<?php

namespace ShopwareAdapter\ServiceBus\QueryHandler\Order;

use PlentyConnector\Connector\ServiceBus\Query\Order\FetchAllOrdersQuery;
use PlentyConnector\Connector\ServiceBus\Query\QueryInterface;
use PlentyConnector\Connector\ServiceBus\QueryHandler\QueryHandlerInterface;
use Shopware\Components\Api\Resource;
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
     * @var Resource\Order
     */
    private $orderResource;

    /**
     * FetchAllOrdersQueryHandler constructor.
     *
     * @param OrderResponseParserInterface $responseParser
     * @param Resource\Order $orderResource
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
                'expression' => '!=',
                'value' => -1,
            ],
        ];

        $orders = $this->orderResource->getList(0, null, $filter);

        $result = array_map(function ($order) {
            return $this->responseParser->parse($this->orderResource->getOne($order['id']));
        }, $orders['data']);

        return $result;
    }
}
