<?php

namespace PlentymarketsAdapter\ServiceBus\QueryHandler\Product;

use PlentyConnector\Connector\ServiceBus\Query\Product\FetchChangedProductsQuery;
use PlentyConnector\Connector\ServiceBus\Query\QueryInterface;
use PlentyConnector\Connector\ServiceBus\QueryHandler\QueryHandlerInterface;
use PlentyConnector\Console\OutputHandler\OutputHandlerInterface;
use PlentymarketsAdapter\PlentymarketsAdapter;
use PlentymarketsAdapter\ReadApi\Item;
use PlentymarketsAdapter\ResponseParser\Product\ProductResponseParserInterface;
use PlentymarketsAdapter\ServiceBus\ChangedDateTimeTrait;
use Psr\Log\LoggerInterface;

/**
 * Class FetchChangedProductsQueryHandler.
 */
class FetchChangedProductsQueryHandler implements QueryHandlerInterface
{
    use ChangedDateTimeTrait;

    /**
     * @var Item
     */
    private $itemApi;

    /**
     * @var ProductResponseParserInterface
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
     * FetchChangedProductsQueryHandler constructor.
     *
     * @param Item                           $itemApi
     * @param ProductResponseParserInterface $responseParser
     * @param LoggerInterface                $logger
     * @param OutputHandlerInterface         $outputHandler
     */
    public function __construct(
        Item $itemApi,
        ProductResponseParserInterface $responseParser,
        LoggerInterface $logger,
        OutputHandlerInterface $outputHandler
    ) {
        $this->itemApi = $itemApi;
        $this->responseParser = $responseParser;
        $this->logger = $logger;
        $this->outputHandler = $outputHandler;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(QueryInterface $query)
    {
        return $query instanceof FetchChangedProductsQuery &&
            $query->getAdapterName() === PlentymarketsAdapter::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(QueryInterface $query)
    {
        $lastCangedTime = $this->getChangedDateTime();
        $currentDateTime = $this->getCurrentDateTime();

        $elements = $this->itemApi->findChanged($lastCangedTime, $currentDateTime);

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

            $parsedElements = array_filter($result);

            foreach ($parsedElements as $parsedElement) {
                $parsedElements[] = $parsedElement;
            }
        }

        $this->outputHandler->finishProgressBar();
        $this->setChangedDateTime($currentDateTime);

        return $parsedElements;
    }
}
