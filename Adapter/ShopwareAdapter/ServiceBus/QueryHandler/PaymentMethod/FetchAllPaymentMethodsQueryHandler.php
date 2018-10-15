<?php

namespace ShopwareAdapter\ServiceBus\QueryHandler\PaymentMethod;

use Doctrine\ORM\EntityManagerInterface;
use Shopware\Models\Payment\Payment;
use Shopware\Models\Payment\Repository;
use ShopwareAdapter\ResponseParser\PaymentMethod\PaymentMethodResponseParserInterface;
use ShopwareAdapter\ShopwareAdapter;
use SystemConnector\ServiceBus\Query\FetchTransferObjectQuery;
use SystemConnector\ServiceBus\Query\QueryInterface;
use SystemConnector\ServiceBus\QueryHandler\QueryHandlerInterface;
use SystemConnector\ServiceBus\QueryType;
use SystemConnector\TransferObject\PaymentMethod\PaymentMethod;

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
        return $query instanceof FetchTransferObjectQuery &&
            $query->getAdapterName() === ShopwareAdapter::NAME &&
            $query->getObjectType() === PaymentMethod::TYPE &&
            $query->getQueryType() === QueryType::ALL;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(QueryInterface $query)
    {
        $elements = $this->repository->getActivePaymentsQuery()->getArrayResult();

        foreach ($elements as $element) {
            $result = $this->responseParser->parse($element);

            if (null === $result) {
                continue;
            }

            yield $result;
        }
    }
}
