<?php

namespace ShopwareAdapter\ServiceBus\QueryHandler\Unit;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use PlentyConnector\Connector\ServiceBus\Query\FetchTransferObjectQuery;
use PlentyConnector\Connector\ServiceBus\Query\QueryInterface;
use PlentyConnector\Connector\ServiceBus\QueryHandler\QueryHandlerInterface;
use PlentyConnector\Connector\ServiceBus\QueryType;
use PlentyConnector\Connector\TransferObject\Unit\Unit;
use Shopware\Models\Article\Unit as UnitModel;
use ShopwareAdapter\ResponseParser\Unit\UnitResponseParserInterface;
use ShopwareAdapter\ShopwareAdapter;

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
        $this->repository = $entityManager->getRepository(UnitModel::class);
        $this->responseParser = $responseParser;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(QueryInterface $query)
    {
        return $query instanceof FetchTransferObjectQuery &&
            $query->getAdapterName() === ShopwareAdapter::NAME &&
            $query->getObjectType() === Unit::TYPE &&
            $query->getQueryType() === QueryType::ALL;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(QueryInterface $query)
    {
        $elements = $this->createUnitsQuery()->getArrayResult();

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
