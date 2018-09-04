<?php

namespace ShopwareAdapter\ServiceBus\QueryHandler\CustomerGroup;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use PlentyConnector\Connector\ServiceBus\Query\FetchTransferObjectQuery;
use PlentyConnector\Connector\ServiceBus\Query\QueryInterface;
use PlentyConnector\Connector\ServiceBus\QueryHandler\QueryHandlerInterface;
use PlentyConnector\Connector\ServiceBus\QueryType;
use PlentyConnector\Connector\TransferObject\CustomerGroup\CustomerGroup;
use Shopware\Models\Customer\Group;
use ShopwareAdapter\ResponseParser\CustomerGroup\CustomerGroupResponseParserInterface;
use ShopwareAdapter\ShopwareAdapter;

class FetchAllCustomerGroupsQueryHandler implements QueryHandlerInterface
{
    /**
     * @var EntityRepository
     */
    private $repository;

    /**
     * @var CustomerGroupResponseParserInterface
     */
    private $responseParser;

    /**
     * FetchAllCustomerGroupsQueryHandler constructor.
     *
     * @param EntityManagerInterface               $entityManager
     * @param CustomerGroupResponseParserInterface $responseParser
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        CustomerGroupResponseParserInterface $responseParser
    ) {
        $this->repository = $entityManager->getRepository(Group::class);
        $this->responseParser = $responseParser;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(QueryInterface $query)
    {
        return $query instanceof FetchTransferObjectQuery &&
            $query->getAdapterName() === ShopwareAdapter::NAME &&
            $query->getObjectType() === CustomerGroup::TYPE &&
            $query->getQueryType() === QueryType::ALL;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(QueryInterface $query)
    {
        $elements = $this->createCustomerGroupsQuery()->getArrayResult();

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
    private function createCustomerGroupsQuery()
    {
        $queryBuilder = $this->repository->createQueryBuilder('groups');
        $queryBuilder->select([
            'groups.id as id',
            'groups.name as name',
        ]);

        $objectQuery = $queryBuilder->getQuery();
        $objectQuery->execute();

        return $objectQuery;
    }
}
