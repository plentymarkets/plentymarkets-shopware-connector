<?php

namespace ShopwareAdapter\ServiceBus\QueryHandler\PaymentStatus;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use PlentyConnector\Connector\ServiceBus\Query\PaymentStatus\FetchAllPaymentStatusesQuery;
use PlentyConnector\Connector\ServiceBus\Query\QueryInterface;
use PlentyConnector\Connector\ServiceBus\QueryHandler\QueryHandlerInterface;
use Shopware\Components\Model\ModelRepository;
use Shopware\Models\Order\Status;
use ShopwareAdapter\ResponseParser\PaymentStatus\PaymentStatusResponseParserInterface;
use ShopwareAdapter\ShopwareAdapter;

/**
 * Class FetchAllPaymentStatusesQueryHandler.
 */
class FetchAllPaymentStatusesQueryHandler implements QueryHandlerInterface
{
    /**
     * @var ModelRepository
     */
    private $repository;

    /**
     * @var PaymentStatusResponseParserInterface
     */
    private $responseParser;

    /**
     * FetchAllPaymentStatusesQueryHandler constructor.
     *
     * @param EntityManagerInterface               $entityManager  ,
     * @param PaymentStatusResponseParserInterface $responseParser
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        PaymentStatusResponseParserInterface $responseParser
    ) {
        $this->repository = $entityManager->getRepository(Status::class);
        $this->responseParser = $responseParser;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(QueryInterface $query)
    {
        return $query instanceof FetchAllPaymentStatusesQuery &&
            $query->getAdapterName() === ShopwareAdapter::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(QueryInterface $query)
    {
        $objectQuery = $this->createPaymentStatusQuery();

        $paymentStatuses = array_map(function ($status) {
            return $this->responseParser->parse($status);
        }, $objectQuery->getArrayResult());

        return array_filter($paymentStatuses);
    }

    /**
     * @return Query
     */
    private function createPaymentStatusQuery()
    {
        $queryBuilder = $this->repository->createQueryBuilder('status');
        $queryBuilder->select([
            'status.id as id',
            'status.name as name',
        ]);
        $queryBuilder->where('status.group = :group');
        $queryBuilder->setParameter('group', Status::GROUP_PAYMENT);

        $objectQuery = $queryBuilder->getQuery();
        $objectQuery->execute();

        return $objectQuery;
    }
}
