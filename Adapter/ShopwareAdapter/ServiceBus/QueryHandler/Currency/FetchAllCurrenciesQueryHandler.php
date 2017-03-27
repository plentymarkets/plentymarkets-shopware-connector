<?php

namespace ShopwareAdapter\ServiceBus\QueryHandler\Currency;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use PlentyConnector\Connector\ServiceBus\Query\Currency\FetchAllCurrenciesQuery;
use PlentyConnector\Connector\ServiceBus\Query\QueryInterface;
use PlentyConnector\Connector\ServiceBus\QueryHandler\QueryHandlerInterface;
use Shopware\Components\Model\ModelRepository;
use Shopware\Models\Shop\Currency;
use ShopwareAdapter\ResponseParser\Currency\CurrencyResponseParserInterface;
use ShopwareAdapter\ShopwareAdapter;

/**
 * Class FetchAllCurrenciesQueryHandler.
 */
class FetchAllCurrenciesQueryHandler implements QueryHandlerInterface
{
    /**
     * @var ModelRepository
     */
    private $repository;

    /**
     * @var CurrencyResponseParserInterface
     */
    private $responseParser;

    /**
     * FetchAllCurrenciesQueryHandler constructor.
     *
     * @param EntityManagerInterface          $entityManager
     * @param CurrencyResponseParserInterface $responseParser
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        CurrencyResponseParserInterface $responseParser
    ) {
        $this->repository = $entityManager->getRepository(Currency::class);
        $this->responseParser = $responseParser;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(QueryInterface $query)
    {
        return $query instanceof FetchAllCurrenciesQuery &&
            $query->getAdapterName() === ShopwareAdapter::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(QueryInterface $query)
    {
        $objectQuery = $this->createCurrenciesQuery();

        $currencies = array_map(function ($currency) {
            return $this->responseParser->parse($currency);
        }, $objectQuery->getArrayResult());

        return array_filter($currencies);
    }

    /**
     * @return Query
     */
    private function createCurrenciesQuery()
    {
        $queryBuilder = $this->repository->createQueryBuilder('currencies');
        $queryBuilder->select([
            'currencies.id as id',
            'currencies.name as name',
            'currencies.currency as currency',
        ]);

        $objectQuery = $queryBuilder->getQuery();
        $objectQuery->execute();

        return $objectQuery;
    }
}
