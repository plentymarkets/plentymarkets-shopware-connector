<?php

namespace PlentymarketsAdapter\QueryBus\QueryHandler\MediaCategory;

use PlentyConnector\Connector\QueryBus\Query\Manufacturer\FetchAllManufacturersQuery;
use PlentyConnector\Connector\QueryBus\Query\MediaCategory\FetchAllMediaCategoriesQuery;
use PlentyConnector\Connector\QueryBus\Query\QueryInterface;
use PlentyConnector\Connector\QueryBus\QueryHandler\QueryHandlerInterface;
use PlentymarketsAdapter\Client\ClientInterface;
use PlentymarketsAdapter\Helper\MediaCategoryHelper;
use PlentymarketsAdapter\PlentymarketsAdapter;
use PlentymarketsAdapter\ResponseParser\ResponseParserInterface;
use Psr\Log\LoggerInterface;
use UnexpectedValueException;

/**
 * Class FetchAllMediaCategoriesQueryHandler
 */
class FetchAllMediaCategoriesQueryHandler implements QueryHandlerInterface
{
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
     * @param MediaCategoryHelper $mediaCategoryHelper
     * @param ResponseParserInterface $responseParser
     */
    public function __construct(
        MediaCategoryHelper $mediaCategoryHelper,
        ResponseParserInterface $responseParser
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
