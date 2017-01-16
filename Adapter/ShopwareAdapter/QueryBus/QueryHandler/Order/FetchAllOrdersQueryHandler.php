<?php

namespace ShopwareAdapter\QueryBus\QueryHandler\Order;

use PlentyConnector\Connector\QueryBus\Query\Order\FetchAllOrdersQuery;
use PlentyConnector\Connector\QueryBus\Query\Order\GetChangedOrderQuery;
use PlentyConnector\Connector\QueryBus\Query\QueryInterface;
use PlentyConnector\Connector\QueryBus\QueryHandler\QueryHandlerInterface;
use Psr\Log\LoggerInterface;
use Shopware\Components\Api\Resource;
use ShopwareAdapter\ResponseParser\ResponseParserInterface;
use ShopwareAdapter\ShopwareAdapter;

/**
 * Class FetchAllOrdersQueryHandler
 */
class FetchAllOrdersQueryHandler implements QueryHandlerInterface
{
    /**
     * @var ResponseParserInterface
     */
    private $responseParser;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Resource\Order
     */
    private $orderResource;

    /**
     * FetchAllOrdersQueryHandler constructor.
     *
     * @param ResponseParserInterface $responseParser
     * @param LoggerInterface $logger
     * @param Resource\Order $orderResource
     */
    public function __construct(
        ResponseParserInterface $responseParser,
        LoggerInterface $logger,
        Resource\Order $orderResource
    ) {
        $this->responseParser = $responseParser;
        $this->logger = $logger;
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
                'property'   => 'status',
                'expression' => '!=',
                'value'      => -1
            ]
        ];

        $orders = $this->orderResource->getList(0, null, $filter);

        $result = array_map(function ($order) {
            return $this->responseParser->parse($this->orderResource->getOne($order['id']));
        }, $orders['data']);

        return $result;
    }
}
