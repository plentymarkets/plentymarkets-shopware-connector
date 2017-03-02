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
     * FetchCategoryQueryHandler constructor.
     *
     * @param ClientInterface $client
     * @param CategoryResponseParserInterface $categoryResponseParser
     * @param LanguageHelper $languageHelper
     */
    public function __construct(
        ClientInterface $client,
        CategoryResponseParserInterface $categoryResponseParser,
        LanguageHelper $languageHelper
    ) {
        $this->client = $client;
        $this->categoryResponseParser = $categoryResponseParser;
        $this->languageHelper = $languageHelper;
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

        $elements = $this->client->request('GET', 'categories', [
            'with' => 'details',
            'type' => 'item',
            'updatedAt' => $lastCangedTime->format(DATE_W3C),
            'lang' => $this->languageHelper->getLanguagesQueryString(),
        ]);

        $elements = array_filter($elements, function ($element) {
            return $element['right'] === 'all';
        });

        $result = [];

        array_walk($elements, function (array $element) use (&$result) {
            if (empty($element['details'])) {
                return;
            }

            $categoriesGrouped = [];
            foreach ($element['details'] as $detail) {
                $categoriesGrouped[$detail['plentyId']][] = $detail;
            }

            foreach ($categoriesGrouped as $plentyId => $details) {
                $parsedElements = $this->categoryResponseParser->parse([
                    'plentyId' => $plentyId,
                    'categoryId' => $element['id'],
                    'parentCategoryId' => $element['parentCategoryId'],
                    'details' => $details,
                ]);

                foreach ($parsedElements as $parsedElement) {
                    $result[] = $parsedElement;
                }
            }
        });

        if (!empty($result)) {
            $this->setChangedDateTime($currentDateTime);
        }

        return array_filter($result);
    }
}
