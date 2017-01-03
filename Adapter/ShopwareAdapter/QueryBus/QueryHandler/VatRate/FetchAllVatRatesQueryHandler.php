<?php

namespace ShopwareAdapter\QueryBus\QueryHandler\VatRate;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use PlentyConnector\Connector\QueryBus\Query\QueryInterface;
use PlentyConnector\Connector\QueryBus\Query\VatRate\FetchAllVatRatesQuery;
use PlentyConnector\Connector\QueryBus\QueryHandler\QueryHandlerInterface;
use Shopware\Components\Model\ModelRepository;
use Shopware\Models\Tax\Tax;
use ShopwareAdapter\ResponseParser\ResponseParserInterface;
use ShopwareAdapter\ShopwareAdapter;

/**
 * Class FetchAllVatRatesQueryHandler
 */
class FetchAllVatRatesQueryHandler implements QueryHandlerInterface
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
     * FetchAllVatRatesQueryHandler constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param ResponseParserInterface $responseParser
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        ResponseParserInterface $responseParser
    ) {
        $this->repository = $entityManager->getRepository(Tax::class);
        $this->responseParser = $responseParser;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(QueryInterface $event)
    {
        return $event instanceof FetchAllVatRatesQuery &&
            $event->getAdapterName() === ShopwareAdapter::getName();
    }

    /**
     * {@inheritdoc}
     */
    public function handle(QueryInterface $event)
    {
        $query = $this->createTaxQuery();

        $vatRates = array_map(function ($vatRate) {
            return $this->responseParser->parse($vatRate);
        }, $query->getArrayResult());

        return array_filter($vatRates);
    }

    /**
     * @return Query
     */
    private function createTaxQuery()
    {
        $queryBuilder = $this->repository->createQueryBuilder('taxes');
        $queryBuilder->select([
            'taxes.id as id',
            'taxes.name as name',
            'taxes.tax as tax',
        ]);

        $query = $queryBuilder->getQuery();
        $query->execute();

        return $query;
    }
}
