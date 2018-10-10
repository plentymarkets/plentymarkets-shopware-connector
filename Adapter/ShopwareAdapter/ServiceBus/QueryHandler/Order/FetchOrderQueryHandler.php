<?php

namespace ShopwareAdapter\ServiceBus\QueryHandler\Order;

use ShopwareAdapter\DataProvider\Order\OrderDataProviderInterface;
use ShopwareAdapter\ResponseParser\Order\OrderResponseParserInterface;
use ShopwareAdapter\ShopwareAdapter;
use SystemConnector\IdentityService\IdentityServiceInterface;
use SystemConnector\ServiceBus\Query\FetchTransferObjectQuery;
use SystemConnector\ServiceBus\Query\QueryInterface;
use SystemConnector\ServiceBus\QueryHandler\QueryHandlerInterface;
use SystemConnector\ServiceBus\QueryType;
use SystemConnector\TransferObject\Order\Order;

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
