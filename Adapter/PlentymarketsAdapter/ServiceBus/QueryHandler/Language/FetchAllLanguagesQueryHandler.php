<?php

namespace PlentymarketsAdapter\ServiceBus\QueryHandler\Language;

use PlentyConnector\Connector\ServiceBus\Query\FetchTransferObjectQuery;
use PlentyConnector\Connector\ServiceBus\Query\QueryInterface;
use PlentyConnector\Connector\ServiceBus\QueryHandler\QueryHandlerInterface;
use PlentyConnector\Connector\ServiceBus\QueryType;
use PlentyConnector\Connector\TransferObject\Language\Language;
use PlentymarketsAdapter\Helper\LanguageHelperInterface;
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
     * @var LanguageHelperInterface
     */
    private $languageHelper;

    /**
     * FetchAllLanguagesQueryHandler constructor.
     *
     * @param LanguageResponseParserInterface $responseParser
     * @param LanguageHelperInterface         $languageHelper
     */
    public function __construct(
        LanguageResponseParserInterface $responseParser,
        LanguageHelperInterface $languageHelper
    ) {
        $this->responseParser = $responseParser;
        $this->languageHelper = $languageHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(QueryInterface $query)
    {
        return $query instanceof FetchTransferObjectQuery &&
            PlentymarketsAdapter::NAME === $query->getAdapterName() &&
            Language::TYPE === $query->getObjectType() &&
            QueryType::ALL === $query->getQueryType();
    }

    /**
     * {@inheritdoc}
     */
    public function handle(QueryInterface $query)
    {
        $elements = $this->languageHelper->getLanguages();

        foreach ($elements as $element) {
            $result = $this->responseParser->parse($element);

            if (null === $result) {
                continue;
            }

            yield $result;
        }
    }
}
