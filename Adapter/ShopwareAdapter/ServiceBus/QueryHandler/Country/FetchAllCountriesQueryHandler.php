<?php

namespace ShopwareAdapter\ServiceBus\QueryHandler\Country;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use Shopware\Models\Country\Country as CountryModel;
use ShopwareAdapter\ResponseParser\Country\CountryResponseParserInterface;
use ShopwareAdapter\ShopwareAdapter;
use SystemConnector\ServiceBus\Query\FetchTransferObjectQuery;
use SystemConnector\ServiceBus\Query\QueryInterface;
use SystemConnector\ServiceBus\QueryHandler\QueryHandlerInterface;
use SystemConnector\ServiceBus\QueryType;
use SystemConnector\TransferObject\Country\Country;

class FetchAllCountriesQueryHandler implements QueryHandlerInterface
{
    /**
     * @var EntityRepository
     */
    private $repository;

    /**
     * @var CountryResponseParserInterface
     */
    private $responseParser;

    public function __construct(
        EntityManagerInterface $entityManager,
        CountryResponseParserInterface $responseParser
    ) {
        $this->repository = $entityManager->getRepository(CountryModel::class);
        $this->responseParser = $responseParser;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(QueryInterface $query): bool
    {
        return $query instanceof FetchTransferObjectQuery &&
            $query->getAdapterName() === ShopwareAdapter::NAME &&
            $query->getObjectType() === Country::TYPE &&
            $query->getQueryType() === QueryType::ALL;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(QueryInterface $query)
    {
        $elements = $this->createCurrenciesQuery()->getArrayResult();

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
    private function createCurrenciesQuery(): Query
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
