<?php

namespace PlentymarketsAdapter\ServiceBus\QueryHandler\Order;

use PlentyConnector\Connector\IdentityService\IdentityServiceInterface;
use PlentyConnector\Connector\ServiceBus\Query\Order\FetchOrderQuery;
use PlentyConnector\Connector\ServiceBus\Query\QueryInterface;
use PlentyConnector\Connector\ServiceBus\QueryHandler\QueryHandlerInterface;
use PlentyConnector\Connector\TransferObject\Order\Order;
use PlentymarketsAdapter\PlentymarketsAdapter;
use PlentymarketsAdapter\ReadApi\Order\Order as OrderApi;
use PlentymarketsAdapter\ResponseParser\Order\OrderResponseParserInterface;

/**
 * Class FetchOrderQueryHandler
 */
class FetchOrderQueryHandler implements QueryHandlerInterface
{
    /**
     * @var Order
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
        return $query instanceof FetchOrderQuery &&
            $query->getAdapterName() === PlentymarketsAdapter::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(QueryInterface $query)
    {
        /**
         * @var FetchOrderQuery $query
         */
        $identity = $this->identityService->findOneBy([
            'objectIdentifier' => $query->getIdentifier(),
            'objectType' => Order::TYPE,
            'adapterName' => PlentymarketsAdapter::NAME,
        ]);

        if (null === $identity) {
            return [];
        }

        $order = $this->api->find($identity->getAdapterIdentifier());

        $result = [$this->responseParser->parse($order)];

        return array_filter($result);
    }
}
