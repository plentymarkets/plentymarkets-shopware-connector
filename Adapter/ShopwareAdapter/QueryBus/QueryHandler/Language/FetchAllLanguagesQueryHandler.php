<?php

namespace ShopwareAdapter\QueryBus\QueryHandler\Language;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use PlentyConnector\Connector\QueryBus\Query\Language\FetchAllLanguagesQuery;
use PlentyConnector\Connector\QueryBus\Query\QueryInterface;
use PlentyConnector\Connector\QueryBus\QueryHandler\QueryHandlerInterface;
use Shopware\Components\Model\ModelRepository;
use Shopware\Models\Shop\Locale;
use ShopwareAdapter\ResponseParser\Language\LanguageResponseParserInterface;
use ShopwareAdapter\ShopwareAdapter;

/**
 * Class FetchAllLanguagesQueryHandler
 */
class FetchAllLanguagesQueryHandler implements QueryHandlerInterface
{
    /**
     * @var ModelRepository
     */
    private $repository;

    /**
     * @var LanguageResponseParserInterface
     */
    private $responseParser;

    /**
     * FetchAllLanguagesQueryHandler constructor.
     *
     * @param EntityManagerInterface $entityManager
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
            'locales.locale as locale'
        ]);

        $objectQuery = $queryBuilder->getQuery();
        $objectQuery->execute();

        return $objectQuery;
    }
}
