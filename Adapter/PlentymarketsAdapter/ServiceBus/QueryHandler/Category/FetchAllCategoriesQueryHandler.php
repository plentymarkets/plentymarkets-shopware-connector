<?php

namespace PlentymarketsAdapter\ServiceBus\QueryHandler\Category;

use PlentyConnector\Connector\ServiceBus\Query\Category\FetchAllCategoriesQuery;
use PlentyConnector\Connector\ServiceBus\Query\QueryInterface;
use PlentyConnector\Connector\ServiceBus\QueryHandler\QueryHandlerInterface;
use PlentymarketsAdapter\Client\ClientInterface;
use PlentymarketsAdapter\Helper\LanguageHelper;
use PlentymarketsAdapter\PlentymarketsAdapter;
use PlentymarketsAdapter\ResponseParser\Category\CategoryResponseParserInterface;

/**
 * Class FetchAllCategoriesQueryHandler
 */
class FetchAllCategoriesQueryHandler implements QueryHandlerInterface
{
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
     * FetchAllCategoriesQueryHandler constructor.
     *
     * @param ClientInterface                 $client
     * @param CategoryResponseParserInterface $categoryResponseParser
     * @param LanguageHelper                  $languageHelper
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
        return $query instanceof FetchAllCategoriesQuery &&
            $query->getAdapterName() === PlentymarketsAdapter::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(QueryInterface $query)
    {
        $elements = $this->client->request('GET', 'categories', [
            'with' => 'details',
            'lang' => implode(',', array_column($this->languageHelper->getLanguages(), 'id')),
        ]);

        $elements = array_filter($elements, function ($element) {
            return $element['type'] === 'item' && $element['right'] === 'all';
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

        return array_filter($result);
    }
}
