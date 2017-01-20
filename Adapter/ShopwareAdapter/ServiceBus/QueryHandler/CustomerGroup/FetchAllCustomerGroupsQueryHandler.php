<?php

namespace ShopwareAdapter\ServiceBus\QueryHandler\CustomerGroup;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use PlentyConnector\Connector\ServiceBus\Query\CustomerGroup\FetchAllCustomerGroupsQuery;
use PlentyConnector\Connector\ServiceBus\Query\QueryInterface;
use PlentyConnector\Connector\ServiceBus\QueryHandler\QueryHandlerInterface;
use Shopware\Components\Model\ModelRepository;
use Shopware\Models\Customer\Group;
use ShopwareAdapter\ResponseParser\CustomerGroup\CustomerGroupResponseParserInterface;
use ShopwareAdapter\ShopwareAdapter;

/**
 * Class FetchAllCustomerGroupsQueryHandler
 */
class FetchAllCustomerGroupsQueryHandler implements QueryHandlerInterface
{
    /**
     * @var ModelRepository
     */
    private $repository;

    /**
     * @var CustomerGroupResponseParserInterface
     */
    private $responseParser;

    /**
     * FetchAllCustomerGroupsQueryHandler constructor.
     *
     * @param EntityManagerInterface $entityManager
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
        return $query instanceof FetchAllCustomerGroupsQuery &&
            $query->getAdapterName() === ShopwareAdapter::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(QueryInterface $query)
    {
        $objectQuery = $this->createCustomerGroupsQuery();

        $customerGroups = array_map(function ($group) {
            return $this->responseParser->parse($group);
        }, $objectQuery->getArrayResult());

        return array_filter($customerGroups);
    }

    /**
     * @return Query
     */
    private function createCustomerGroupsQuery()
    {
        $queryBuilder = $this->repository->createQueryBuilder('groups');
        $queryBuilder->select([
            'groups.id as id',
            'groups.name as name'
        ]);

        $objectQuery = $queryBuilder->getQuery();
        $objectQuery->execute();

        return $objectQuery;
    }
}
