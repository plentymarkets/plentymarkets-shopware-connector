<?php

namespace ShopwareAdapter\ServiceBus\QueryHandler\OrderStatus;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use PlentyConnector\Connector\ServiceBus\Query\FetchTransferObjectQuery;
use PlentyConnector\Connector\ServiceBus\Query\QueryInterface;
use PlentyConnector\Connector\ServiceBus\QueryHandler\QueryHandlerInterface;
use PlentyConnector\Connector\ServiceBus\QueryType;
use PlentyConnector\Connector\TransferObject\OrderStatus\OrderStatus;
use Shopware\Models\Order\Status;
use ShopwareAdapter\ResponseParser\OrderStatus\OrderStatusResponseParserInterface;
use ShopwareAdapter\ShopwareAdapter;

/**
 * Class FetchAllOrderStatusesQueryHandler
 */
class FetchAllOrderStatusesQueryHandler implements QueryHandlerInterface
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var OrderStatusResponseParserInterface
     */
    private $responseParser;

    /**
     * FetchAllOrderStatusesQueryHandler constructor.
     *
     * @param EntityManagerInterface             $entityManager
     * @param OrderStatusResponseParserInterface $responseParser
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        OrderStatusResponseParserInterface $responseParser
    ) {
        $this->entityManager  = $entityManager;
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
        $queryBuilder = $this->entityManager->createQueryBuilder();
        $queryBuilder->from(Status::class, 'status');
        $queryBuilder->select([
            'status.id as id',
            'status.name as name',
            'status.description as description',
        ]);
        $queryBuilder->where('status.group = :group');
        $queryBuilder->setParameter('group', Status::GROUP_STATE);

        $objectQuery = $queryBuilder->getQuery();
        $objectQuery->execute();

        return $objectQuery;
    }
}
