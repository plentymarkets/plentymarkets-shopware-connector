<?php

namespace ShopwareAdapter\ServiceBus\QueryHandler\Order;

use PlentyConnector\Connector\IdentityService\IdentityServiceInterface;
use PlentyConnector\Connector\ServiceBus\Query\FetchTransferObjectQuery;
use PlentyConnector\Connector\ServiceBus\Query\QueryInterface;
use PlentyConnector\Connector\ServiceBus\QueryHandler\QueryHandlerInterface;
use PlentyConnector\Connector\ServiceBus\QueryType;
use PlentyConnector\Connector\TransferObject\Order\Order;
use ShopwareAdapter\DataProvider\Order\OrderDataProviderInterface;
use ShopwareAdapter\ResponseParser\Order\OrderResponseParserInterface;
use ShopwareAdapter\ShopwareAdapter;

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
     * @var OrderDataProviderInterface
     */
    private $dataProvider;

    /**
     * FetchOrderQueryHandler constructor.
     *
     * @param OrderResponseParserInterface $responseParser
     * @param IdentityServiceInterface     $identityService
     * @param OrderDataProviderInterface   $dataProvider
     */
    public function __construct(
        OrderResponseParserInterface $responseParser,
        IdentityServiceInterface $identityService,
        OrderDataProviderInterface $dataProvider
    ) {
        $this->responseParser = $responseParser;
        $this->identityService = $identityService;
        $this->dataProvider = $dataProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(QueryInterface $query)
    {
        return $query instanceof FetchTransferObjectQuery &&
            $query->getAdapterName() === ShopwareAdapter::NAME &&
            $query->getObjectType() === Order::TYPE &&
            $query->getQueryType() === QueryType::ONE;
    }

    /**
     * {@inheritdoc}
     *
     * @param FetchTransferObjectQuery $query
     */
    public function handle(QueryInterface $query)
    {
        $identity = $this->identityService->findOneBy([
            'objectIdentifier' => $query->getObjectIdentifier(),
            'objectType' => Order::TYPE,
            'adapterName' => ShopwareAdapter::NAME,
        ]);

        if (null === $identity) {
            return [];
        }

        $order = $this->dataProvider->getOrderDetails($identity->getAdapterIdentifier());
        $order = $this->responseParser->parse($order);

        return array_filter($order);
    }
}
