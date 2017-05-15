<?php

namespace ShopwareAdapter\ServiceBus\QueryHandler\Order;

use PlentyConnector\Connector\IdentityService\IdentityServiceInterface;
use PlentyConnector\Connector\ServiceBus\Query\FetchQueryInterface;
use PlentyConnector\Connector\ServiceBus\Query\Order\FetchOrderQuery;
use PlentyConnector\Connector\ServiceBus\Query\QueryInterface;
use PlentyConnector\Connector\ServiceBus\QueryHandler\QueryHandlerInterface;
use PlentyConnector\Connector\TransferObject\Order\Order;
use Shopware\Components\Api\Resource\Order as OrderResource;
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
     * @var OrderResource
     */
    private $orderResource;

    /**
     * FetchOrderQueryHandler constructor.
     *
     * @param OrderResponseParserInterface $responseParser
     * @param IdentityServiceInterface     $identityService
     * @param OrderResource                $orderResource
     */
    public function __construct(
        OrderResponseParserInterface $responseParser,
        IdentityServiceInterface $identityService,
        OrderResource $orderResource
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

        if (null === $identity) {
            return [];
        }

        $order = $this->orderResource->getOne($identity->getAdapterIdentifier());

        $order = $this->responseParser->parse($order);

        return array_filter($order);
    }
}
