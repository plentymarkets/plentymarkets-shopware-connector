<?php

namespace ShopwareAdapter\QueryBus\QueryHandler\Order;

use PlentyConnector\Connector\QueryBus\Query\Order\FetchChangedOrdersQuery;
use PlentyConnector\Connector\QueryBus\Query\QueryInterface;
use PlentyConnector\Connector\QueryBus\QueryHandler\QueryHandlerInterface;
use Psr\Log\LoggerInterface;
use Shopware\Components\Api\Resource;
use ShopwareAdapter\QueryBus\ChangedDateTimeTrait;
use ShopwareAdapter\ResponseParser\Order\OrderResponseParserInterface;
use ShopwareAdapter\ShopwareAdapter;

/**
 * Class FetchChangedOrdersQueryHandler
 */
class FetchChangedOrdersQueryHandler implements QueryHandlerInterface
{
    use ChangedDateTimeTrait;

    /**
     * @var OrderResponseParserInterface
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
     * FetchChangedOrdersQueryHandler constructor.
     *
     * @param OrderResponseParserInterface $responseParser
     * @param LoggerInterface $logger
     * @param Resource\Order $orderResource
     */
    public function __construct(
        OrderResponseParserInterface $responseParser,
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
        // TODO how to identify changed orders?

        throw new \Exception("Not implemented!");

        $filter = [
            [
                'property' => 'status',
                'expression' => '!=',
                'value' => -1
            ],
        ];

        $orders = $this->orderResource->getList(0, null, $filter);

        $result = array_map(function ($order) {
            return $this->responseParser->parse($this->orderResource->getOne($order['id']));
        }, $orders);

        return $result;
    }
}
