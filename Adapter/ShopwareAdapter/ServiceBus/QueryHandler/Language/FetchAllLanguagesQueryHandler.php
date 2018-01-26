<?php

namespace ShopwareAdapter\ServiceBus\QueryHandler\Language;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use PlentyConnector\Connector\ServiceBus\Query\FetchTransferObjectQuery;
use PlentyConnector\Connector\ServiceBus\Query\QueryInterface;
use PlentyConnector\Connector\ServiceBus\QueryHandler\QueryHandlerInterface;
use PlentyConnector\Connector\ServiceBus\QueryType;
use PlentyConnector\Connector\TransferObject\Language\Language;
use Shopware\Models\Shop\Locale as LocaleModel;
use ShopwareAdapter\ResponseParser\Language\LanguageResponseParserInterface;
use ShopwareAdapter\ShopwareAdapter;

/**
 * Class FetchAllLanguagesQueryHandler
 */
class FetchAllLanguagesQueryHandler implements QueryHandlerInterface
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var LanguageResponseParserInterface
     */
    private $responseParser;

    /**
     * FetchAllLanguagesQueryHandler constructor.
     *
     * @param EntityManagerInterface          $entityManager
     * @param LanguageResponseParserInterface $responseParser
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        LanguageResponseParserInterface $responseParser
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
            $query->getObjectType() === Language::TYPE &&
            $query->getQueryType() === QueryType::ALL;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(QueryInterface $query)
    {
        $elements = $this->createLocalesQuery()->getArrayResult();

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
    private function createLocalesQuery()
    {
        $queryBuilder = $this->entityManager->createQueryBuilder();
        $queryBuilder->from(LocaleModel::class, 'locales');
        $queryBuilder->select([
            'locales.id as id',
            'locales.language as name',
            'locales.locale as locale',
        ]);

        $objectQuery = $queryBuilder->getQuery();
        $objectQuery->execute();

        return $objectQuery;
    }
}
