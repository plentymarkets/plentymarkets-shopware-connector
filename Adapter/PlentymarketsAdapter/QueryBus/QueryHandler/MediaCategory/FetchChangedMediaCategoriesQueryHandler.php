<?php

namespace PlentymarketsAdapter\QueryBus\QueryHandler\MediaCategory;

use PlentyConnector\Connector\ConfigService\ConfigServiceInterface;
use PlentyConnector\Connector\QueryBus\Query\MediaCategory\FetchChangedMediaCategoriesQuery;
use PlentyConnector\Connector\QueryBus\Query\QueryInterface;
use PlentyConnector\Connector\QueryBus\QueryHandler\QueryHandlerInterface;
use PlentymarketsAdapter\Helper\MediaCategoryHelper;
use PlentymarketsAdapter\PlentymarketsAdapter;
use PlentymarketsAdapter\ResponseParser\ResponseParserInterface;

/**
 * Class FetchChangedMediaCategoriesQueryHandler.
 */
class FetchChangedMediaCategoriesQueryHandler implements QueryHandlerInterface
{
    /**
     * @var ConfigServiceInterface
     */
    private $config;

    /**
     * @var MediaCategoryHelper
     */
    private $mediaCategoryHelper;

    /**
     * @var ResponseParserInterface
     */
    private $responseParser;

    /**
     * FetchAllMediaCategoriesQueryHandler constructor.
     *
     * @param ConfigServiceInterface $config
     * @param MediaCategoryHelper $mediaCategoryHelper
     * @param ResponseParserInterface $responseParser
     */
    public function __construct(
        ConfigServiceInterface $config,
        MediaCategoryHelper $mediaCategoryHelper,
        ResponseParserInterface $responseParser
    ) {
        $this->config = $config;
        $this->mediaCategoryHelper = $mediaCategoryHelper;
        $this->responseParser = $responseParser;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(QueryInterface $query)
    {
        return $query instanceof FetchChangedMediaCategoriesQuery &&
            $query->getAdapterName() === PlentymarketsAdapter::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(QueryInterface $query)
    {
        $synced = $this->config->get('PlentymarketsAdapter.MediaCategoriesSynched', null);

        if (null !== $synced) {
            return [];
        }

        $mediaCategories = array_map(function ($category) {
            return $this->responseParser->parse($category);
        }, $this->mediaCategoryHelper->getCategories());

        $this->config->set('PlentymarketsAdapter.MediaCategoriesSynched', true);

        return array_filter($mediaCategories);
    }
}
