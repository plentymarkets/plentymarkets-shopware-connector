<?php

namespace ShopwareAdapter\QueryBus\Handler\PaymentMethod;

use Doctrine\ORM\EntityManagerInterface;
use PlentyConnector\Connector\QueryBus\Handler\QueryHandlerInterface;
use PlentyConnector\Connector\QueryBus\Query\PaymentMethod\FetchAllPaymentMethodsQuery;
use PlentyConnector\Connector\QueryBus\Query\QueryInterface;
use Psr\Log\LoggerInterface;
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
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var Repository
     */
    private $repository;

    /**
     * FetchAllPaymentMethodsHandler constructor.
     *
     * @param ResponseParserInterface $responseParser
     * @param LoggerInterface $logger
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(
        ResponseParserInterface $responseParser,
        LoggerInterface $logger,
        EntityManagerInterface $entityManager
    ) {
        $this->responseParser = $responseParser;
        $this->logger = $logger;
        $this->entityManager = $entityManager;
        $this->repository = $entityManager->getRepository(Payment::class);
    }

    /**
     * {@inheritdoc}
     */
    public function supports(QueryInterface $event)
    {
        return
            $event instanceof FetchAllPaymentMethodsQuery &&
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
        $shopwarePaymentMethods = $query->getArrayResult();

        $result = [];
        foreach ($shopwarePaymentMethods as $paymentMethod) {
            try {
                $result[] = $this->responseParser->parse($paymentMethod);
            } catch (Exception $e) {
                $this->logger->error($e->getMessage());
            }
        }

        return $result;
    }
}
