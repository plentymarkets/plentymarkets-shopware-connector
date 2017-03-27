<?php

namespace PlentymarketsAdapter\ServiceBus\QueryHandler\MediaCategory;

use PlentyConnector\Connector\ServiceBus\Query\MediaCategory\FetchAllMediaCategoriesQuery;
use PlentyConnector\Connector\ServiceBus\Query\QueryInterface;
use PlentyConnector\Connector\ServiceBus\QueryHandler\QueryHandlerInterface;
use PlentymarketsAdapter\Helper\MediaCategoryHelper;
use PlentymarketsAdapter\PlentymarketsAdapter;
use PlentymarketsAdapter\ResponseParser\MediaCategory\MediaCategoryResponseParserInterface;

/**
 * Class FetchAllMediaCategoriesQueryHandler.
 */
class FetchAllMediaCategoriesQueryHandler implements QueryHandlerInterface
{
    /**
     * @var MediaCategoryHelper
     */
    private $mediaCategoryHelper;

    /**
     * @var MediaCategoryResponseParserInterface
     */
    private $responseParser;

    /**
     * FetchAllMediaCategoriesQueryHandler constructor.
     *
     * @param MediaCategoryHelper                  $mediaCategoryHelper
     * @param MediaCategoryResponseParserInterface $responseParser
     */
    public function __construct(
        MediaCategoryHelper $mediaCategoryHelper,
        MediaCategoryResponseParserInterface $responseParser
    ) {
        $this->mediaCategoryHelper = $mediaCategoryHelper;
        $this->responseParser = $responseParser;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(QueryInterface $query)
    {
        return $query instanceof FetchAllMediaCategoriesQuery &&
            $query->getAdapterName() === PlentymarketsAdapter::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(QueryInterface $query)
    {
        $mediaCategories = array_map(function ($category) {
            return $this->responseParser->parse($category);
        }, $this->mediaCategoryHelper->getCategories());

        return array_filter($mediaCategories);
    }
}
