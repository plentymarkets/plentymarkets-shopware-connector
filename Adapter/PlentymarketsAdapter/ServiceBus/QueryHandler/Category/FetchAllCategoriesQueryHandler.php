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
    private $categoryResponseParser;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * FetchAllCategoriesQueryHandler constructor.
     *
     * @param Category                        $categoryApi
     * @param CategoryResponseParserInterface $categoryResponseParser
     * @param LoggerInterface                 $logger
     */
    public function __construct(
        Category $categoryApi,
        CategoryResponseParserInterface $categoryResponseParser,
        LoggerInterface $logger
    ) {
        $this->categoryApi = $categoryApi;
        $this->categoryResponseParser = $categoryResponseParser;
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
        $elements = $this->categoryApi->findAll();

        foreach ($elements as $element) {
            if ($element['right'] !== 'all') {
                $this->logger->notice('unsupported category rights');

                continue;
            }

            $parsedElements = array_filter($this->categoryResponseParser->parse($element));

            foreach ($parsedElements as $parsedElement) {
                yield $parsedElement;
            }
        }
    }
}
