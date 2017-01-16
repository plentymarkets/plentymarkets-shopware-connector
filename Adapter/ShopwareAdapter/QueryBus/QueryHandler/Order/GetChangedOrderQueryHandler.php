<?php

namespace ShopwareAdapter\QueryBus\QueryHandler\Order;

use Exception;
use PlentyConnector\Connector\QueryBus\Query\Order\GetChangedOrderQuery;
use PlentyConnector\Connector\QueryBus\Query\QueryInterface;
use PlentyConnector\Connector\QueryBus\QueryHandler\QueryHandlerInterface;
use Psr\Log\LoggerInterface;
use Shopware\Components\Api\Resource;
use ShopwareAdapter\ResponseParser\ResponseParserInterface;
use ShopwareAdapter\ShopwareAdapter;

/**
 * Class GetChangedOrderQueryHandler
 */
class GetChangedOrderQueryHandler implements QueryHandlerInterface
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
     * GetChangedOrderQueryHandler constructor.
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
        return $query instanceof GetChangedOrderQuery &&
            $query->getAdapterName() === ShopwareAdapter::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(QueryInterface $query)
    {
        $orders = $this->orderResource->getList(0, null)['data'];

        // ignore cancelled orders
        $orders = array_filter($orders, function ($item) {
            return $item['orderStatusId'] != -1;
        });

        $result = [];
        foreach ($orders as $order) {
            try {
                $result[] = $this->responseParser->parseOrder($this->orderResource->getOne($order['id']));
            } catch (Exception $exception) {
                $this->logger->error($exception->getMessage());
            }
        }

        return $result;
    }
}
