<?php

namespace PlentymarketsAdapter\ServiceBus\QueryHandler\Category;

use PlentyConnector\Connector\ServiceBus\Query\Category\FetchAllCategoriesQuery;
use PlentyConnector\Connector\ServiceBus\Query\QueryInterface;
use PlentyConnector\Connector\ServiceBus\QueryHandler\QueryHandlerInterface;
use PlentyConnector\Console\OutputHandler\OutputHandlerInterface;
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
     * @var OutputHandlerInterface
     */
    private $outputHandler;

    /**
     * FetchAllCategoriesQueryHandler constructor.
     *
     * @param Category                        $categoryApi
     * @param CategoryResponseParserInterface $responseParser
     * @param LoggerInterface                 $logger
     * @param OutputHandlerInterface          $outputHandler
     */
    public function __construct(
        Category $categoryApi,
        CategoryResponseParserInterface $responseParser,
        LoggerInterface $logger,
        OutputHandlerInterface $outputHandler
    ) {
        $this->categoryApi = $categoryApi;
        $this->responseParser = $responseParser;
        $this->logger = $logger;
        $this->outputHandler = $outputHandler;
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

        $this->outputHandler->startProgressBar(count($elements));

        $parsedElements = [];
        foreach ($elements as $element) {
            try {
                $result = $this->responseParser->parse($element);
            } catch (Exception $exception) {
                $this->logger->error($exception->getMessage());

                $result = null;
            }

            $this->outputHandler->advanceProgressBar();

            if (empty($result)) {
                continue;
            }

            $result = array_filter($result);

            foreach ($result as $parsedElement) {
                $parsedElements[] = $parsedElement;
            }
        }

        $this->outputHandler->finishProgressBar();

        return $parsedElements;
    }
}
