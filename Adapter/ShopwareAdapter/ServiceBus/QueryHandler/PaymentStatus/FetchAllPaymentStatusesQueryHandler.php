<?php

namespace ShopwareAdapter\ServiceBus\QueryHandler\PaymentStatus;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use PlentyConnector\Connector\ServiceBus\Query\FetchTransferObjectQuery;
use PlentyConnector\Connector\ServiceBus\Query\QueryInterface;
use PlentyConnector\Connector\ServiceBus\QueryHandler\QueryHandlerInterface;
use PlentyConnector\Connector\ServiceBus\QueryType;
use PlentyConnector\Connector\TransferObject\PaymentStatus\PaymentStatus;
use Shopware\Models\Order\Status;
use ShopwareAdapter\ResponseParser\PaymentStatus\PaymentStatusResponseParserInterface;
use ShopwareAdapter\ShopwareAdapter;

/**
 * Class FetchAllPaymentStatusesQueryHandler
 */
class FetchAllPaymentStatusesQueryHandler implements QueryHandlerInterface
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var PaymentStatusResponseParserInterface
     */
    private $responseParser;

    /**
     * FetchAllPaymentStatusesQueryHandler constructor.
     *
     * @param EntityManagerInterface               $entityManager
     * @param PaymentStatusResponseParserInterface $responseParser
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        PaymentStatusResponseParserInterface $responseParser
    ) {
        $this->entityManager = $entityManager;
        $this->responseParser = $responseParser;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(QueryInterface $query)
    {
        return $query instanceof FetchTransferObjectQuery &&
            $query->getAdapterName() === ShopwareAdapter::NAME &&
            $query->getObjectType() === PaymentStatus::TYPE &&
            $query->getQueryType() === QueryType::ALL;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(QueryInterface $query)
    {
        $elements = $this->createPaymentStatusQuery()->getArrayResult();

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
    private function createPaymentStatusQuery()
    {
        $queryBuilder = $this->entityManager->createQueryBuilder();
        $queryBuilder->from(Status::class, 'status');
        $queryBuilder->select([
            'status.id as id',
            'status.name as name',
            'status.description as description',
        ]);
        $queryBuilder->where('status.group = :group');
        $queryBuilder->setParameter('group', Status::GROUP_PAYMENT);

        $objectQuery = $queryBuilder->getQuery();
        $objectQuery->execute();

        return $objectQuery;
    }
}
