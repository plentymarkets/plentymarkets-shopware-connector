<?php

namespace ShopwareAdapter\QueryBus\QueryHandler\Unit;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use PlentyConnector\Connector\QueryBus\Query\QueryInterface;
use PlentyConnector\Connector\QueryBus\Query\Unit\FetchAllUnitsQuery;
use PlentyConnector\Connector\QueryBus\QueryHandler\QueryHandlerInterface;
use Shopware\Components\Model\ModelRepository;
use Shopware\Models\Article\Unit;
use ShopwareAdapter\ResponseParser\ResponseParserInterface;
use ShopwareAdapter\ShopwareAdapter;

/**
 * Class FetchAllUnitsHandler
 */
class FetchAllUnitsHandler implements QueryHandlerInterface
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
     * FetchAllUnitsHandler constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param ResponseParserInterface $responseParser
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        ResponseParserInterface $responseParser
    ) {
        $this->repository = $entityManager->getRepository(Unit::class);
        $this->responseParser = $responseParser;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(QueryInterface $event)
    {
        return $event instanceof FetchAllUnitsQuery &&
            $event->getAdapterName() === ShopwareAdapter::getName();
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

        $query = $queryBuilder->getQuery();
        $query->execute();

        return $query;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(QueryInterface $event)
    {
        $query = $this->createUnitsQuery();

        $units = array_map(function ($unit) {
            return $this->responseParser->parse($unit);
        }, $query->getArrayResult());

        return array_filter($units);
    }
}
