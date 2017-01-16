<?php

namespace ShopwareAdapter\QueryBus\QueryHandler\Order;

use PlentyConnector\Connector\IdentityService\IdentityServiceInterface;
use PlentyConnector\Connector\QueryBus\Query\FetchOrderQuery;
use PlentyConnector\Connector\QueryBus\Query\QueryInterface;
use PlentyConnector\Connector\QueryBus\QueryHandler\QueryHandlerInterface;
use PlentyConnector\Connector\TransferObject\Order\Order;
use Psr\Log\LoggerInterface;
use Shopware\Components\Api\Resource;
use ShopwareAdapter\ResponseParser\ResponseParserInterface;
use ShopwareAdapter\ShopwareAdapter;

/**
 * Class FetchOrderQueryHandler
 */
class FetchOrderQueryHandler implements QueryHandlerInterface
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
     * @var IdentityServiceInterface
     */
    private $identityService;

    /**
     * @var Resource\Order
     */
    private $orderResource;

    /**
     * GetChangedOrderQueryHandler constructor.
     *
     * @param ResponseParserInterface $responseParser
     * @param LoggerInterface $logger
     * @param IdentityServiceInterface $identityService
     * @param Resource\Order $orderResource
     */
    public function __construct(
        ResponseParserInterface $responseParser,
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
    public function supports(QueryInterface $event)
    {
        return $event instanceof FetchOrderQuery &&
            $event->getAdapterName() === ShopwareAdapter::getName();
    }

    /**
     * {@inheritdoc}
     */
    public function handle(QueryInterface $event)
    {
        $identity = $this->identityService->findOneBy([
            'objectIdentifier' => $event->getIdentifier(),
            'objectType' => Order::getType(),
            'adapterName' => ShopwareAdapter::getName(),
        ]);

        $order = $this->orderResource->getOne($identity->getAdapterIdentifier());

        return $this->responseParser->parse($order);
    }
}
