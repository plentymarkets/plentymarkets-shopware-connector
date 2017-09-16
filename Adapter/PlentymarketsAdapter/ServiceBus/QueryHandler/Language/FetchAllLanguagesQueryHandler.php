<?php

namespace PlentymarketsAdapter\ServiceBus\QueryHandler\Language;

use PlentyConnector\Connector\ServiceBus\Query\FetchTransferObjectQuery;
use PlentyConnector\Connector\ServiceBus\Query\QueryInterface;
use PlentyConnector\Connector\ServiceBus\QueryHandler\QueryHandlerInterface;
use PlentyConnector\Connector\ServiceBus\QueryType;
use PlentyConnector\Connector\TransferObject\Language\Language;
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
     * @param LanguageHelper                  $languageHelper
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
        return $query instanceof FetchTransferObjectQuery &&
            $query->getAdapterName() === PlentymarketsAdapter::NAME &&
            $query->getObjectType() === Language::TYPE &&
            $query->getQueryType() === QueryType::ALL;
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
