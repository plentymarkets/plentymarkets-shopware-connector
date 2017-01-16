<?php

namespace ShopwareAdapter\QueryBus\QueryHandler\CustomerGroup;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use PlentyConnector\Connector\QueryBus\Query\CustomerGroup\FetchAllCustomerGroupsQuery;
use PlentyConnector\Connector\QueryBus\Query\QueryInterface;
use PlentyConnector\Connector\QueryBus\QueryHandler\QueryHandlerInterface;
use Shopware\Components\Model\ModelRepository;
use Shopware\Models\Customer\Group;
use ShopwareAdapter\ResponseParser\ResponseParserInterface;
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
     * @var ResponseParserInterface
     */
    private $responseParser;

    /**
     * FetchAllCustomerGroupsQueryHandler constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param ResponseParserInterface $responseParser
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        ResponseParserInterface $responseParser
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
