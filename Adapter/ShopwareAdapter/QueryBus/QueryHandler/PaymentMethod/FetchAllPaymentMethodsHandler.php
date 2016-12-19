<?php

namespace ShopwareAdapter\QueryBus\QueryHandler\PaymentMethod;

use Doctrine\ORM\EntityManagerInterface;
use PlentyConnector\Connector\QueryBus\QueryHandler\QueryHandlerInterface;
use PlentyConnector\Connector\QueryBus\Query\PaymentMethod\FetchAllPaymentMethodsQuery;
use PlentyConnector\Connector\QueryBus\Query\QueryInterface;
use Shopware\Models\Payment\Payment;
use Shopware\Models\Payment\Repository;
use ShopwareAdapter\ResponseParser\ResponseParserInterface;
use ShopwareAdapter\ShopwareAdapter;

/**
 * Class FetchAllPaymentMethodsHandler
 */
class FetchAllPaymentMethodsHandler implements QueryHandlerInterface
{
    /**
     * @var ResponseParserInterface
     */
    private $responseParser;

    /**
     * @var Repository
     */
    private $repository;

    /**
     * FetchAllPaymentMethodsHandler constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param ResponseParserInterface $responseParser
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        ResponseParserInterface $responseParser
    ) {
        $this->repository = $entityManager->getRepository(Payment::class);
        $this->responseParser = $responseParser;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(QueryInterface $event)
    {
        return $event instanceof FetchAllPaymentMethodsQuery &&
            $event->getAdapterName() === ShopwareAdapter::getName();
    }

    /**
     * TODO: Refaktor the foreach loop
     *
     * {@inheritdoc}
     */
    public function handle(QueryInterface $event)
    {
        $query = $this->repository->getActivePaymentsQuery();

        $paymentMethods = array_map(function($paymentMethod) {
            return $this->responseParser->parse($paymentMethod);
        }, $query->getArrayResult());

        return array_filter($paymentMethods);
    }
}
