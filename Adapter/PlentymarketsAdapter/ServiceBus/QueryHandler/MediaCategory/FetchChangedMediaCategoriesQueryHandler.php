<?php

namespace PlentymarketsAdapter\ServiceBus\QueryHandler\MediaCategory;

use PlentyConnector\Connector\ConfigService\ConfigServiceInterface;
use PlentyConnector\Connector\ServiceBus\Query\MediaCategory\FetchChangedMediaCategoriesQuery;
use PlentyConnector\Connector\ServiceBus\Query\QueryInterface;
use PlentyConnector\Connector\ServiceBus\QueryHandler\QueryHandlerInterface;
use PlentymarketsAdapter\Helper\MediaCategoryHelper;
use PlentymarketsAdapter\PlentymarketsAdapter;
use PlentymarketsAdapter\ResponseParser\MediaCategory\MediaCategoryResponseParserInterface;

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
     * @var MediaCategoryResponseParserInterface
     */
    private $responseParser;

    /**
     * FetchAllMediaCategoriesQueryHandler constructor.
     *
     * @param ConfigServiceInterface               $config
     * @param MediaCategoryHelper                  $mediaCategoryHelper
     * @param MediaCategoryResponseParserInterface $responseParser
     */
    public function __construct(
        ConfigServiceInterface $config,
        MediaCategoryHelper $mediaCategoryHelper,
        MediaCategoryResponseParserInterface $responseParser
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
