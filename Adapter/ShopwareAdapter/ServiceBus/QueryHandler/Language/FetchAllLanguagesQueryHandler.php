<?php

namespace ShopwareAdapter\ServiceBus\QueryHandler\Language;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use PlentyConnector\Connector\ServiceBus\Query\Language\FetchAllLanguagesQuery;
use PlentyConnector\Connector\ServiceBus\Query\QueryInterface;
use PlentyConnector\Connector\ServiceBus\QueryHandler\QueryHandlerInterface;
use Shopware\Models\Shop\Locale;
use ShopwareAdapter\ResponseParser\Language\LanguageResponseParserInterface;
use ShopwareAdapter\ShopwareAdapter;

/**
 * Class FetchAllLanguagesQueryHandler
 */
class FetchAllLanguagesQueryHandler implements QueryHandlerInterface
{
    /**
     * @var EntityRepository
     */
    private $repository;

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
        $this->repository = $entityManager->getRepository(Locale::class);
        $this->responseParser = $responseParser;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(QueryInterface $query)
    {
        return $query instanceof FetchAllLanguagesQuery &&
            $query->getAdapterName() === ShopwareAdapter::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(QueryInterface $query)
    {
        $objectQuery = $this->createLocalesQuery();

        $languages = array_map(function ($language) {
            return $this->responseParser->parse($language);
        }, $objectQuery->getArrayResult());

        return array_filter($languages);
    }

    /**
     * @return Query
     */
    private function createLocalesQuery()
    {
        $queryBuilder = $this->repository->createQueryBuilder('locales');
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
