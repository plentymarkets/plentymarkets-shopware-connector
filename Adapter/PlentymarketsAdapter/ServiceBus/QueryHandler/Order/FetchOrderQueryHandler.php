<?php

namespace PlentymarketsAdapter\ServiceBus\QueryHandler\Order;

use PlentyConnector\Connector\IdentityService\IdentityServiceInterface;
use PlentyConnector\Connector\ServiceBus\Query\FetchTransferObjectQuery;
use PlentyConnector\Connector\ServiceBus\Query\QueryInterface;
use PlentyConnector\Connector\ServiceBus\QueryHandler\QueryHandlerInterface;
use PlentyConnector\Connector\ServiceBus\QueryType;
use PlentyConnector\Connector\TransferObject\Order\Order;
use PlentymarketsAdapter\PlentymarketsAdapter;
use PlentymarketsAdapter\ReadApi\Order\Order as OrderApi;
use PlentymarketsAdapter\ResponseParser\Order\OrderResponseParserInterface;

class FetchOrderQueryHandler implements QueryHandlerInterface
{
    /**
     * @var OrderApi
     */
    private $api;

    /**
     * @var IdentityServiceInterface
     */
    private $identityService;

    /**
     * @var OrderResponseParserInterface
     */
    private $responseParser;

    /**
     * FetchAllOrdersQueryHandler constructor.
     *
     * @param OrderApi                     $api
     * @param IdentityServiceInterface     $identityService
     * @param OrderResponseParserInterface $responseParser
     */
    public function __construct(
        OrderApi $api,
        IdentityServiceInterface $identityService,
        OrderResponseParserInterface $responseParser
    ) {
        $this->api = $api;
        $this->identityService = $identityService;
        $this->responseParser = $responseParser;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(QueryInterface $query)
    {
        return $query instanceof FetchTransferObjectQuery &&
            $query->getAdapterName() === PlentymarketsAdapter::NAME &&
            $query->getObjectType() === Order::TYPE &&
            $query->getQueryType() === QueryType::ONE;
    }

    /**
     * {@inheritdoc}
     *
     * @var FetchTransferObjectQuery $query
     */
    public function handle(QueryInterface $query)
    {
        $identity = $this->identityService->findOneBy([
            'objectIdentifier' => $query->getObjectIdentifier(),
            'objectType' => Order::TYPE,
            'adapterName' => PlentymarketsAdapter::NAME,
        ]);

        if (null === $identity) {
            return [];
        }

        $orderData = $this->api->find($identity->getAdapterIdentifier());

        $order = $this->responseParser->parse($orderData);

        return array_filter($order);
    }
}
