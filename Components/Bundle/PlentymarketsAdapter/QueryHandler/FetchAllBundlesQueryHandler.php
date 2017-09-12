<?php

namespace PlentyConnector\Components\Bundle\PlentymarketsAdapter\QueryHandler;

use PlentyConnector\Components\Bundle\PlentymarketsAdapter\ResponseParser\BundleResponseParser;
use PlentyConnector\Components\Bundle\Query\FetchAllBundlesQuery;
use PlentyConnector\Connector\ServiceBus\Query\QueryInterface;
use PlentyConnector\Connector\ServiceBus\QueryHandler\QueryHandlerInterface;
use PlentyConnector\Console\OutputHandler\OutputHandlerInterface;
use PlentymarketsAdapter\PlentymarketsAdapter;
use PlentymarketsAdapter\ReadApi\Item;
use Psr\Log\LoggerInterface;

/**
 * Class FetchAllBundlesQueryHandler.
 */
class FetchAllBundlesQueryHandler implements QueryHandlerInterface
{
    /**
     * @var Item
     */
    private $itemApi;

    /**
     * @var BundleResponseParser
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
     * FetchAllBundlesQueryHandler constructor.
     *
     * @param Item                   $itemApi
     * @param BundleResponseParser   $responseParser
     * @param LoggerInterface        $logger
     * @param OutputHandlerInterface $outputHandler
     */
    public function __construct(
        Item $itemApi,
        BundleResponseParser $responseParser,
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
        return $query instanceof FetchAllBundlesQuery &&
            $query->getAdapterName() === PlentymarketsAdapter::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(QueryInterface $query)
    {
        $elements = $this->itemApi->findAll();

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

        return $parsedElements;
    }
}
