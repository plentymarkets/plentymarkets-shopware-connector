<?php

namespace PlentymarketsAdapter\QueryBus\QueryHandler\Category;

use PlentyConnector\Connector\IdentityService\IdentityServiceInterface;
use PlentyConnector\Connector\QueryBus\Query\Category\FetchCategoryQuery;
use PlentyConnector\Connector\QueryBus\Query\FetchQueryInterface;
use PlentyConnector\Connector\QueryBus\Query\QueryInterface;
use PlentyConnector\Connector\QueryBus\QueryHandler\QueryHandlerInterface;
use PlentymarketsAdapter\Client\ClientInterface;
use PlentymarketsAdapter\Helper\LanguageHelper;
use PlentymarketsAdapter\PlentymarketsAdapter;
use PlentymarketsAdapter\ResponseParser\Category\CategoryResponseParserInterface;

/**
 * Class FetchCategoryQueryHandler
 */
class FetchCategoryQueryHandler implements QueryHandlerInterface
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
     * @var IdentityServiceInterface
     */
    private $identityService;

    /**
     * FetchCategoryQueryHandler constructor.
     *
     * @param ClientInterface $client
     * @param CategoryResponseParserInterface $categoryResponseParser
     * @param LanguageHelper $languageHelper
     * @param IdentityServiceInterface $identityService
     */
    public function __construct(
        ClientInterface $client,
        CategoryResponseParserInterface $categoryResponseParser,
        LanguageHelper $languageHelper,
        IdentityServiceInterface $identityService
    ) {
        $this->client = $client;
        $this->categoryResponseParser = $categoryResponseParser;
        $this->languageHelper = $languageHelper;
        $this->identityService = $identityService;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(QueryInterface $query)
    {
        return $query instanceof FetchCategoryQuery &&
            $query->getAdapterName() === PlentymarketsAdapter::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(QueryInterface $query)
    {
        /**
         * @var FetchQueryInterface $query
         */
        $identity = $this->identityService->findOneBy([
            'objectIdentifier' => $query->getIdentifier(),
            'objectType' => Category::TYPE,
            'adapterName' => PlentymarketsAdapter::NAME,
        ]);

        $element = $this->client->request('GET', 'categories/' . $identity->getAdapterIdentifier(), [
            'with' => 'details',
            'lang' => implode(',', array_column($this->languageHelper->getLanguages(), 'id'))
        ]);

        if ($element['type'] !== 'item' || $element['right'] !== 'all') {
            return [];
        }

        if (empty($element['details'])) {
            return [];
        }

        $result = [];

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

        return array_filter($result);
    }
}
