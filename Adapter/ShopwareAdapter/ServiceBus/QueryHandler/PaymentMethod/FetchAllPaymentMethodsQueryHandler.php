<?php

namespace ShopwareAdapter\ServiceBus\QueryHandler\PaymentMethod;

use Doctrine\ORM\EntityManagerInterface;
use PlentyConnector\Connector\ServiceBus\Query\PaymentMethod\FetchAllPaymentMethodsQuery;
use PlentyConnector\Connector\ServiceBus\Query\QueryInterface;
use PlentyConnector\Connector\ServiceBus\QueryHandler\QueryHandlerInterface;
use Shopware\Models\Payment\Payment;
use Shopware\Models\Payment\Repository;
use ShopwareAdapter\ResponseParser\PaymentMethod\PaymentMethodResponseParserInterface;
use ShopwareAdapter\ShopwareAdapter;

/**
 * Class FetchAllPaymentMethodsQueryHandler
 */
class FetchAllPaymentMethodsQueryHandler implements QueryHandlerInterface
{
    /**
     * @var Repository
     */
    private $repository;

    /**
     * @var PaymentMethodResponseParserInterface
     */
    private $responseParser;

    /**
     * FetchAllPaymentMethodsQueryHandler constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param PaymentMethodResponseParserInterface $responseParser
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        PaymentMethodResponseParserInterface $responseParser
    ) {
        $this->repository = $entityManager->getRepository(Payment::class);
        $this->responseParser = $responseParser;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(QueryInterface $query)
    {
        return $query instanceof FetchAllPaymentMethodsQuery &&
            $query->getAdapterName() === ShopwareAdapter::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(QueryInterface $query)
    {
        $objectQuery = $this->repository->getActivePaymentsQuery();

        $paymentMethods = array_map(function ($paymentMethod) {
            return $this->responseParser->parse($paymentMethod);
        }, $objectQuery->getArrayResult());

        return array_filter($paymentMethods);
    }
}
