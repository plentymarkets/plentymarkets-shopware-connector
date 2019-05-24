<?php

namespace ShopwareAdapter\ServiceBus\QueryHandler\Currency;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use Shopware\Models\Shop\Currency as CurrencyModel;
use ShopwareAdapter\ResponseParser\Currency\CurrencyResponseParserInterface;
use ShopwareAdapter\ShopwareAdapter;
use SystemConnector\ServiceBus\Query\FetchTransferObjectQuery;
use SystemConnector\ServiceBus\Query\QueryInterface;
use SystemConnector\ServiceBus\QueryHandler\QueryHandlerInterface;
use SystemConnector\ServiceBus\QueryType;
use SystemConnector\TransferObject\Currency\Currency;

class FetchAllCurrenciesQueryHandler implements QueryHandlerInterface
{
    /**
     * @var EntityRepository
     */
    private $repository;

    /**
     * @var CurrencyResponseParserInterface
     */
    private $responseParser;

    public function __construct(
        EntityManagerInterface $entityManager,
        CurrencyResponseParserInterface $responseParser
    ) {
        $this->repository = $entityManager->getRepository(CurrencyModel::class);
        $this->responseParser = $responseParser;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(QueryInterface $query): bool
    {
        return $query instanceof FetchTransferObjectQuery &&
            $query->getAdapterName() === ShopwareAdapter::NAME &&
            $query->getObjectType() === Currency::TYPE &&
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
