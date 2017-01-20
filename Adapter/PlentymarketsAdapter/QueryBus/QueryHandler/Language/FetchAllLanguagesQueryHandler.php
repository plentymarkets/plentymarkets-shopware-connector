<?php

namespace PlentymarketsAdapter\QueryBus\QueryHandler\Language;

use PlentyConnector\Connector\QueryBus\Query\Language\FetchAllLanguagesQuery;
use PlentyConnector\Connector\QueryBus\Query\QueryInterface;
use PlentyConnector\Connector\QueryBus\QueryHandler\QueryHandlerInterface;
use PlentymarketsAdapter\Helper\LanguageHelper;
use PlentymarketsAdapter\PlentymarketsAdapter;
use PlentymarketsAdapter\ResponseParser\Language\LanguageResponseParserInterface;

/**
 * Class FetchAllLanguagesQueryHandler
 */
class FetchAllLanguagesQueryHandler implements QueryHandlerInterface
{
    /**
     * @var LanguageResponseParserInterface
     */
    private $responseParser;

    /**
     * @var LanguageHelper
     */
    private $languageHelper;

    /**
     * FetchAllLanguagesQueryHandler constructor.
     *
     * @param LanguageResponseParserInterface $responseParser
     * @param LanguageHelper $languageHelper
     */
    public function __construct(
        LanguageResponseParserInterface $responseParser,
        LanguageHelper $languageHelper
    ) {
        $this->responseParser = $responseParser;
        $this->languageHelper = $languageHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(QueryInterface $query)
    {
        return $query instanceof FetchAllLanguagesQuery &&
            $query->getAdapterName() === PlentymarketsAdapter::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(QueryInterface $query)
    {
        $languages = array_map(function ($language) {
            return $this->responseParser->parse($language);
        }, $this->languageHelper->getLanguages());

        return array_filter($languages);
    }
}
