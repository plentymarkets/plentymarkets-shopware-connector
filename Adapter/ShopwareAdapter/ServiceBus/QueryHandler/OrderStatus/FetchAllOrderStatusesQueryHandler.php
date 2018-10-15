<?php

namespace ShopwareAdapter\ServiceBus\QueryHandler\OrderStatus;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use Shopware\Models\Order\Status;
use ShopwareAdapter\ResponseParser\OrderStatus\OrderStatusResponseParserInterface;
use ShopwareAdapter\ShopwareAdapter;
use SystemConnector\ServiceBus\Query\FetchTransferObjectQuery;
use SystemConnector\ServiceBus\Query\QueryInterface;
use SystemConnector\ServiceBus\QueryHandler\QueryHandlerInterface;
use SystemConnector\ServiceBus\QueryType;
use SystemConnector\TransferObject\OrderStatus\OrderStatus;

class FetchAllOrderStatusesQueryHandler implements QueryHandlerInterface
{
    /**
     * @var EntityRepository
     */
    private $repository;

    /**
     * @var OrderStatusResponseParserInterface
     */
    private $responseParser;

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
        return $query instanceof FetchTransferObjectQuery &&
            $query->getAdapterName() === ShopwareAdapter::NAME &&
            $query->getObjectType() === OrderStatus::TYPE &&
            $query->getQueryType() === QueryType::ALL;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(QueryInterface $query)
    {
        $elements = $this->createOrderStatusQuery()->getArrayResult();

        foreach ($elements as $element) {
            $result = $this->responseParser->parse($element);

            if (null === $result) {
                continue;
            }

            yield $result;
        }
    }

    /**
     * @return Query
     */
    private function createOrderStatusQuery()
    {
        $queryBuilder = $this->repository->createQueryBuilder('status');
        $queryBuilder->select([
            'status.id as id',
            'status.name as name',
        ]);
        $queryBuilder->where('status.group = :group');
        $queryBuilder->setParameter('group', Status::GROUP_STATE);

        $objectQuery = $queryBuilder->getQuery();
        $objectQuery->execute();

        return $objectQuery;
    }
}
