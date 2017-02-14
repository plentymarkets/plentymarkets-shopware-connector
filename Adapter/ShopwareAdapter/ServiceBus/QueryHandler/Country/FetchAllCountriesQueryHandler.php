<?php

namespace ShopwareAdapter\ServiceBus\QueryHandler\Country;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use PlentyConnector\Connector\ServiceBus\Query\Country\FetchAllCountriesQuery;
use PlentyConnector\Connector\ServiceBus\Query\QueryInterface;
use PlentyConnector\Connector\ServiceBus\QueryHandler\QueryHandlerInterface;
use Shopware\Components\Model\ModelRepository;
use Shopware\Models\Country\Country;
use ShopwareAdapter\ResponseParser\Country\CountryResponseParserInterface;
use ShopwareAdapter\ShopwareAdapter;

/**
 * Class FetchAllCountriesQueryHandler
 */
class FetchAllCountriesQueryHandler implements QueryHandlerInterface
{
    /**
     * @var ModelRepository
     */
    private $repository;

    /**
     * @var CountryResponseParserInterface
     */
    private $responseParser;

    /**
     * FetchAllCountriesQueryHandler constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param CountryResponseParserInterface $responseParser
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        CountryResponseParserInterface $responseParser
    ) {
        $this->repository = $entityManager->getRepository(Country::class);
        $this->responseParser = $responseParser;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(QueryInterface $query)
    {
        return $query instanceof FetchAllCountriesQuery &&
            $query->getAdapterName() === ShopwareAdapter::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(QueryInterface $query)
    {
        $objectQuery = $this->createCurrenciesQuery();

        $countries = array_map(function ($country) {
            return $this->responseParser->parse($country);
        }, $objectQuery->getArrayResult());

        return array_filter($countries);
    }

    /**
     * @return Query
     */
    private function createCurrenciesQuery()
    {
        $queryBuilder = $this->repository->createQueryBuilder('countries');
        $queryBuilder->select([
            'countries.id as id',
            'countries.name as name',
            'countries.iso as countryCode',
        ]);

        $objectQuery = $queryBuilder->getQuery();
        $objectQuery->execute();

        return $objectQuery;
    }
}
