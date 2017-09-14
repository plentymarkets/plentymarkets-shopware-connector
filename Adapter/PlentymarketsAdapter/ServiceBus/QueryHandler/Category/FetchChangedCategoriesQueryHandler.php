<?php

namespace PlentymarketsAdapter\ServiceBus\QueryHandler\Category;

use PlentyConnector\Connector\ServiceBus\Query\Category\FetchChangedCategoriesQuery;
use PlentyConnector\Connector\ServiceBus\Query\QueryInterface;
use PlentyConnector\Connector\ServiceBus\QueryHandler\QueryHandlerInterface;
use PlentyConnector\Console\OutputHandler\OutputHandlerInterface;
use PlentymarketsAdapter\PlentymarketsAdapter;
use PlentymarketsAdapter\ReadApi\Category\Category;
use PlentymarketsAdapter\ResponseParser\Category\CategoryResponseParserInterface;
use PlentymarketsAdapter\ServiceBus\ChangedDateTimeTrait;
use Psr\Log\LoggerInterface;

/**
 * Class FetchChangedCategoriesQueryHandler.
 */
class FetchChangedCategoriesQueryHandler implements QueryHandlerInterface
{
    use ChangedDateTimeTrait;

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
     * FetchChangedCategoriesQueryHandler constructor.
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

        $elements = $this->categoryApi->findChanged($lastCangedTime, $currentDateTime);

        $this->outputHandler->startProgressBar(count($elements));

        foreach ($elements as $element) {
            try {
                $result = $this->responseParser->parse($element);
            } catch (Exception $exception) {
                $this->logger->error($exception->getMessage());

                $result = null;
            }

            if (empty($result)) {
                $result = [];
            }

            $result = array_filter($result);

            foreach ($result as $parsedElement) {
                yield $parsedElement;
            }

            $this->outputHandler->advanceProgressBar();
        }

        $this->outputHandler->finishProgressBar();
        $this->setChangedDateTime($currentDateTime);
    }
}
