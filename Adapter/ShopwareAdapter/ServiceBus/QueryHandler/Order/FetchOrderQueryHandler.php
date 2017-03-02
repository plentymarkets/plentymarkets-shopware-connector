<?php

namespace ShopwareAdapter\ServiceBus\QueryHandler\Order;

use PlentyConnector\Connector\IdentityService\IdentityServiceInterface;
use PlentyConnector\Connector\ServiceBus\Query\FetchQueryInterface;
use PlentyConnector\Connector\ServiceBus\Query\Order\FetchOrderQuery;
use PlentyConnector\Connector\ServiceBus\Query\QueryInterface;
use PlentyConnector\Connector\ServiceBus\QueryHandler\QueryHandlerInterface;
use PlentyConnector\Connector\TransferObject\Order\Order;
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
     * @param IdentityServiceInterface $identityService
     * @param Resource\Order $orderResource
     */
    public function __construct(
        OrderResponseParserInterface $responseParser,
        IdentityServiceInterface $identityService,
        Resource\Order $orderResource
    ) {
        $this->responseParser = $responseParser;
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
            'objectIdentifier' => $query->getPayload(),
            'objectType' => Order::TYPE,
            'adapterName' => ShopwareAdapter::NAME,
        ]);

        $order = $this->orderResource->getOne($identity->getAdapterIdentifier());

        return $this->responseParser->parse($order);
    }
}
