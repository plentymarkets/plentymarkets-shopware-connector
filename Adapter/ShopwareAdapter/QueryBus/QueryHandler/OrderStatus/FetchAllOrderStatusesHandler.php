<?php

namespace ShopwareAdapter\QueryBus\QueryHandler\OrderStatus;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use PlentyConnector\Connector\QueryBus\Query\OrderStatus\FetchAllOrderStatusesQuery;
use PlentyConnector\Connector\QueryBus\Query\QueryInterface;
use PlentyConnector\Connector\QueryBus\QueryHandler\QueryHandlerInterface;
use Shopware\Components\Model\ModelRepository;
use Shopware\Models\Order\Status;
use ShopwareAdapter\ResponseParser\ResponseParserInterface;
use ShopwareAdapter\ShopwareAdapter;

/**
 * Class FetchAllOrderStatusesHandler
 */
class FetchAllOrderStatusesHandler implements QueryHandlerInterface
{
    /**
     * @var ModelRepository
     */
    private $repository;

    /**
     * @var ResponseParserInterface
     */
    private $responseParser;

    /**
     * FetchAllOrderStatusesHandler constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param ResponseParserInterface $responseParser
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        ResponseParserInterface $responseParser
    ) {
        $this->repository = $entityManager->getRepository(Status::class);
        $this->responseParser = $responseParser;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(QueryInterface $event)
    {
        return $event instanceof FetchAllOrderStatusesQuery &&
            $event->getAdapterName() === ShopwareAdapter::getName();
    }

    /**
     * {@inheritdoc}
     */
    public function handle(QueryInterface $event)
    {
        $query = $this->createOrderStatusQuery();

        $orderStatuses = array_map(function ($status) {
            return $this->responseParser->parse($status);
        }, $query->getArrayResult());

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

        $query = $queryBuilder->getQuery();
        $query->execute();

        return $query;
    }
}
