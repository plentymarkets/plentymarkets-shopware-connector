<?php

namespace PlentymarketsAdapter\ServiceBus\QueryHandler\Category;

use PlentyConnector\Connector\ServiceBus\Query\Category\FetchAllCategoriesQuery;
use PlentyConnector\Connector\ServiceBus\Query\QueryInterface;
use PlentyConnector\Connector\ServiceBus\QueryHandler\QueryHandlerInterface;
use PlentymarketsAdapter\PlentymarketsAdapter;
use PlentymarketsAdapter\ReadApi\Category\Category;
use PlentymarketsAdapter\ResponseParser\Category\CategoryResponseParserInterface;
use Psr\Log\LoggerInterface;

/**
 * Class FetchAllCategoriesQueryHandler
 */
class FetchAllCategoriesQueryHandler implements QueryHandlerInterface
{
    /**
     * @var Category
     */
    private $categoryApi;

    /**
     * @var CategoryResponseParserInterface
     */
    private $responseParser;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * FetchAllCategoriesQueryHandler constructor.
     *
     * @param Category                        $categoryApi
     * @param CategoryResponseParserInterface $responseParser
     * @param LoggerInterface                 $logger
     */
    public function __construct(
        Category $categoryApi,
        CategoryResponseParserInterface $responseParser,
        LoggerInterface $logger
    ) {
        $this->categoryApi = $categoryApi;
        $this->responseParser = $responseParser;
        $this->logger = $logger;
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
        $categories = $this->categoryApi->findAll();

        $parsedElements = [];
        foreach ($categories as $category) {
            try {
                $result = $this->responseParser->parse($category);
            } catch (Exception $exception) {
                $this->logger->error($exception->getMessage());

                $result = null;
            }

            if (empty($result)) {
                continue;
            }

            $parsedElements = array_filter($result);

            foreach ($parsedElements as $parsedElement) {
                $parsedElements[] = $parsedElement;
            }
        }

        return $parsedElements;
    }
}
