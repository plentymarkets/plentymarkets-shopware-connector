<?php

namespace PlentymarketsAdapter\ServiceBus\QueryHandler\Category;

use PlentyConnector\Connector\ServiceBus\Query\Category\FetchChangedCategoriesQuery;
use PlentyConnector\Connector\ServiceBus\Query\QueryInterface;
use PlentyConnector\Connector\ServiceBus\QueryHandler\QueryHandlerInterface;
use PlentymarketsAdapter\Client\ClientInterface;
use PlentymarketsAdapter\Helper\LanguageHelper;
use PlentymarketsAdapter\PlentymarketsAdapter;
use PlentymarketsAdapter\ResponseParser\Category\CategoryResponseParserInterface;
use PlentymarketsAdapter\ServiceBus\ChangedDateTimeTrait;
use Psr\Log\LoggerInterface;

/**
 * Class FetchChangedCategoriesQueryHandler.
 */
class FetchChangedCategoriesQueryHandler implements QueryHandlerInterface
{
    use ChangedDateTimeTrait;

    /**
     * @var ClientInterface
     */
    private $client;

    /**
     * @var CategoryResponseParserInterface
     */
    private $categoryResponseParser;

    /**
     * @var LanguageHelper
     */
    private $languageHelper;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * FetchChangedCategoriesQueryHandler constructor.
     *
     * @param ClientInterface                 $client
     * @param CategoryResponseParserInterface $categoryResponseParser
     * @param LanguageHelper                  $languageHelper
     * @param LoggerInterface                 $logger
     */
    public function __construct(
        ClientInterface $client,
        CategoryResponseParserInterface $categoryResponseParser,
        LanguageHelper $languageHelper,
        LoggerInterface $logger
    ) {
        $this->client = $client;
        $this->categoryResponseParser = $categoryResponseParser;
        $this->languageHelper = $languageHelper;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(QueryInterface $query)
    {
        return $query instanceof FetchChangedCategoriesQuery &&
            $query->getAdapterName() === PlentymarketsAdapter::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(QueryInterface $query)
    {
        $lastCangedTime = $this->getChangedDateTime();
        $currentDateTime = $this->getCurrentDateTime();

        $elements = $this->client->getIterator('categories', [
            'with' => 'details,clients',
            'type' => 'item',
            'updatedAt' => $lastCangedTime->format(DATE_W3C),
            'lang' => $this->languageHelper->getLanguagesQueryString(),
        ]);

        $result = [];
        foreach ($elements as $element) {
            if ($element['right'] !== 'all') {
                $this->logger->notice('unsupported category rights');

                continue;
            }

            $parsedElements = $this->categoryResponseParser->parse($element);

            foreach ($parsedElements as $parsedElement) {
                $result[] = $parsedElement;
            }
        }

        if (!empty($result)) {
            $this->setChangedDateTime($currentDateTime);
        }

        return array_filter($result);
    }
}
