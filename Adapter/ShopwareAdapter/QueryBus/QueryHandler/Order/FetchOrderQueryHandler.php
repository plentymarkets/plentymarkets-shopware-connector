<?php

namespace ShopwareAdapter\QueryBus\QueryHandler\Order;

use PlentyConnector\Connector\IdentityService\IdentityServiceInterface;
use PlentyConnector\Connector\QueryBus\Query\FetchQueryInterface;
use PlentyConnector\Connector\QueryBus\Query\Order\FetchOrderQuery;
use PlentyConnector\Connector\QueryBus\Query\QueryInterface;
use PlentyConnector\Connector\QueryBus\QueryHandler\QueryHandlerInterface;
use PlentyConnector\Connector\TransferObject\Order\Order;
use Psr\Log\LoggerInterface;
use Shopware\Components\Api\Resource;
use ShopwareAdapter\ResponseParser\Order\OrderResponseParserInterface;
use ShopwareAdapter\ShopwareAdapter;

/**
 * Class FetchOrderQueryHandler
 */
class FetchOrderQueryHandler implements QueryHandlerInterface
{
    /**
     * @var OrderResponseParserInterface
     */
    private $responseParser;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var IdentityServiceInterface
     */
    private $identityService;

    /**
     * @var Resource\Order
     */
    private $orderResource;

    /**
     * FetchOrderQueryHandler constructor.
     *
     * @param OrderResponseParserInterface $responseParser
     * @param LoggerInterface $logger
     * @param IdentityServiceInterface $identityService
     * @param Resource\Order $orderResource
     */
    public function __construct(
        OrderResponseParserInterface $responseParser,
        LoggerInterface $logger,
        IdentityServiceInterface $identityService,
        Resource\Order $orderResource
    ) {
        $this->responseParser = $responseParser;
        $this->logger = $logger;
        $this->identityService = $identityService;
        $this->orderResource = $orderResource;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(QueryInterface $query)
    {
        return $query instanceof FetchOrderQuery &&
            $query->getAdapterName() === ShopwareAdapter::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(QueryInterface $query)
    {
        /**
         * @var FetchQueryInterface $event
         */
        $identity = $this->identityService->findOneBy([
            'objectIdentifier' => $event->getIdentifier(),
            'objectType' => Order::TYPE,
            'adapterName' => ShopwareAdapter::NAME,
        ]);

        $order = $this->orderResource->getOne($identity->getAdapterIdentifier());

        return $this->responseParser->parse($order);
    }
}
