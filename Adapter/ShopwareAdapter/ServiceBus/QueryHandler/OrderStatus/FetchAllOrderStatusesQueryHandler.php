<?php

namespace ShopwareAdapter\ServiceBus\QueryHandler\OrderStatus;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use PlentyConnector\Connector\ServiceBus\Query\OrderStatus\FetchAllOrderStatusesQuery;
use PlentyConnector\Connector\ServiceBus\Query\QueryInterface;
use PlentyConnector\Connector\ServiceBus\QueryHandler\QueryHandlerInterface;
use Shopware\Components\Model\ModelRepository;
use Shopware\Models\Order\Status;
use ShopwareAdapter\ResponseParser\OrderStatus\OrderStatusResponseParserInterface;
use ShopwareAdapter\ShopwareAdapter;

/**
 * Class FetchAllOrderStatusesQueryHandler
 */
class FetchAllOrderStatusesQueryHandler implements QueryHandlerInterface
{
    /**
     * @var ModelRepository
     */
    private $repository;

    /**
     * @var OrderStatusResponseParserInterface
     */
    private $responseParser;

    /**
     * FetchAllOrderStatusesQueryHandler constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param OrderStatusResponseParserInterface $responseParser
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        OrderStatusResponseParserInterface $responseParser
    ) {
        $this->repository = $entityManager->getRepository(Status::class);
        $this->responseParser = $responseParser;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(QueryInterface $query)
    {
        return $query instanceof FetchAllOrderStatusesQuery &&
            $query->getAdapterName() === ShopwareAdapter::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(QueryInterface $query)
    {
        $objectQuery = $this->createOrderStatusQuery();

        $orderStatuses = array_map(function ($status) {
            return $this->responseParser->parse($status);
        }, $objectQuery->getArrayResult());

        return array_filter($orderStatuses);
    }

    /**
     * @return Query
     */
    private function createOrderStatusQuery()
    {
        $queryBuilder = $this->repository->createQueryBuilder('status');
        $queryBuilder->select([
            'status.id as id',
            'status.name as name'
        ]);
        $queryBuilder->where('status.group = :group');
        $queryBuilder->setParameter('group', Status::GROUP_STATE);

        $objectQuery = $queryBuilder->getQuery();
        $objectQuery->execute();

        return $objectQuery;
    }
}
