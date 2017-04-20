<?php

namespace ShopwareAdapter\ServiceBus\QueryHandler\Payment;

use PlentyConnector\Connector\IdentityService\IdentityServiceInterface;
use PlentyConnector\Connector\ServiceBus\Query\FetchQueryInterface;
use PlentyConnector\Connector\ServiceBus\Query\Payment\FetchPaymentQuery;
use PlentyConnector\Connector\ServiceBus\Query\QueryInterface;
use PlentyConnector\Connector\ServiceBus\QueryHandler\QueryHandlerInterface;
use PlentyConnector\Connector\TransferObject\Payment\Payment;
use Shopware\Components\Api\Resource\Order as OrderResource;
use ShopwareAdapter\ResponseParser\Payment\PaymentResponseParserInterface;
use ShopwareAdapter\ShopwareAdapter;

/**
 * Class FetchPaymentQueryHandler
 */
class FetchPaymentQueryHandler implements QueryHandlerInterface
{
    /**
     * @var PaymentResponseParserInterface
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
     * FetchPaymentQueryHandler constructor.
     *
     * @param PaymentResponseParserInterface $responseParser
     * @param IdentityServiceInterface $identityService
     * @param OrderResource $orderResource
     */
    public function __construct(
        PaymentResponseParserInterface $responseParser,
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
        return $query instanceof FetchPaymentQuery &&
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
            'objectType' => Payment::TYPE,
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
