<?php

namespace ShopwareAdapter\ServiceBus\QueryHandler\PaymentMethod;

use Doctrine\ORM\EntityManagerInterface;
use PlentyConnector\Connector\ServiceBus\Query\FetchTransferObjectQuery;
use PlentyConnector\Connector\ServiceBus\Query\QueryInterface;
use PlentyConnector\Connector\ServiceBus\QueryHandler\QueryHandlerInterface;
use PlentyConnector\Connector\ServiceBus\QueryType;
use PlentyConnector\Connector\TransferObject\PaymentMethod\PaymentMethod;
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
     * @param EntityManagerInterface               $entityManager
     * @param PaymentMethodResponseParserInterface $responseParser
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        PaymentMethodResponseParserInterface $responseParser
    ) {
        $this->repository     = $entityManager->getRepository(Payment::class);
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
