<?php

namespace ShopwareAdapter\ServiceBus\QueryHandler\Currency;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use PlentyConnector\Connector\ServiceBus\Query\FetchTransferObjectQuery;
use PlentyConnector\Connector\ServiceBus\Query\QueryInterface;
use PlentyConnector\Connector\ServiceBus\QueryHandler\QueryHandlerInterface;
use PlentyConnector\Connector\ServiceBus\QueryType;
use PlentyConnector\Connector\TransferObject\Currency\Currency;
use Shopware\Models\Shop\Currency as CurrencyModel;
use ShopwareAdapter\ResponseParser\Currency\CurrencyResponseParserInterface;
use ShopwareAdapter\ShopwareAdapter;

/**
 * Class FetchAllCurrenciesQueryHandler
 */
class FetchAllCurrenciesQueryHandler implements QueryHandlerInterface
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

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
        $this->entityManager  = $entityManager;
        $this->responseParser = $responseParser;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(QueryInterface $query)
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
    private function createCurrenciesQuery()
    {
        $queryBuilder = $this->entityManager->createQueryBuilder();
        $queryBuilder->from(CurrencyModel::class, 'currencies');
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
