<?php

namespace ShopwareAdapter\QueryBus\Handler\PaymentMethod;

use Doctrine\ORM\EntityManagerInterface;
use PlentyConnector\Connector\QueryBus\Handler\QueryHandlerInterface;
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
     * @param ResponseParserInterface $responseParser
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(
        ResponseParserInterface $responseParser,
        EntityManagerInterface $entityManager
    ) {
        $this->responseParser = $responseParser;
        $this->repository = $entityManager->getRepository(Payment::class);
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
        $paymentMethods = $query->getArrayResult();

        $paymentMethods = array_map(function($paymentMethod) {
            return $this->responseParser->parse($paymentMethod);
        }, $paymentMethods);

        return array_filter($paymentMethods);
    }
}
