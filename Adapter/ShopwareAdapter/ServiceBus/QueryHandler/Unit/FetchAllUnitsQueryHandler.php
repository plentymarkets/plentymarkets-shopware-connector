<?php

namespace ShopwareAdapter\ServiceBus\QueryHandler\Unit;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use PlentyConnector\Connector\ServiceBus\Query\QueryInterface;
use PlentyConnector\Connector\ServiceBus\Query\Unit\FetchAllUnitsQuery;
use PlentyConnector\Connector\ServiceBus\QueryHandler\QueryHandlerInterface;
use Shopware\Models\Article\Unit;
use ShopwareAdapter\ResponseParser\Unit\UnitResponseParserInterface;
use ShopwareAdapter\ShopwareAdapter;

/**
 * Class FetchAllUnitsQueryHandler
 */
class FetchAllUnitsQueryHandler implements QueryHandlerInterface
{
    /**
     * @var EntityRepository
     */
    private $repository;

    /**
     * @var UnitResponseParserInterface
     */
    private $responseParser;

    /**
     * FetchAllUnitsQueryHandler constructor.
     *
     * @param EntityManagerInterface      $entityManager
     * @param UnitResponseParserInterface $responseParser
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        UnitResponseParserInterface $responseParser
    ) {
        $this->repository = $entityManager->getRepository(Unit::class);
        $this->responseParser = $responseParser;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(QueryInterface $query)
    {
        return $query instanceof FetchAllUnitsQuery &&
            $query->getAdapterName() === ShopwareAdapter::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(QueryInterface $query)
    {
        $objectQuery = $this->createUnitsQuery();

        $units = array_map(function ($unit) {
            return $this->responseParser->parse($unit);
        }, $objectQuery->getArrayResult());

        return array_filter($units);
    }

    /**
     * @return Query
     */
    private function createUnitsQuery()
    {
        $queryBuilder = $this->repository->createQueryBuilder('units');
        $queryBuilder->select([
            'units.id as id',
            'units.name as name',
            'units.unit as unit',
        ]);

        $objectQuery = $queryBuilder->getQuery();
        $objectQuery->execute();

        return $objectQuery;
    }
}
