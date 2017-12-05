<?php

namespace PlentyConnector\Components\Bundle\PlentymarketsAdapter\QueryHandler;

use Exception;
use PlentyConnector\Components\Bundle\PlentymarketsAdapter\ResponseParser\BundleResponseParser;
use PlentyConnector\Components\Bundle\TransferObject\Bundle;
use PlentyConnector\Connector\ServiceBus\Query\FetchTransferObjectQuery;
use PlentyConnector\Connector\ServiceBus\Query\QueryInterface;
use PlentyConnector\Connector\ServiceBus\QueryHandler\QueryHandlerInterface;
use PlentyConnector\Connector\ServiceBus\QueryType;
use PlentyConnector\Console\OutputHandler\OutputHandlerInterface;
use PlentymarketsAdapter\PlentymarketsAdapter;
use PlentymarketsAdapter\ReadApi\Item;
use PlentymarketsAdapter\ServiceBus\ChangedDateTimeTrait;
use Psr\Log\LoggerInterface;

/**
 * Class FetchChangedBundlesQueryHandler.
 */
class FetchChangedBundlesQueryHandler implements QueryHandlerInterface
{
    use ChangedDateTimeTrait;

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
     * FetchChangedBundlesQueryHandler constructor.
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
        $this->itemApi        = $itemApi;
        $this->responseParser = $responseParser;
        $this->logger         = $logger;
        $this->outputHandler  = $outputHandler;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(QueryInterface $query)
    {
        return $query instanceof FetchTransferObjectQuery &&
            $query->getAdapterName() === PlentymarketsAdapter::NAME &&
            $query->getObjectType() === Bundle::TYPE &&
            $query->getQueryType() === QueryType::CHANGED;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(QueryInterface $query)
    {
        $lastCangedTime  = $this->getChangedDateTime();
        $currentDateTime = $this->getCurrentDateTime();

        $elements = $this->itemApi->findChanged($lastCangedTime, $currentDateTime);

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

            $parsedElements = array_filter($result);

            foreach ($parsedElements as $parsedElement) {
                yield $parsedElement;
            }

            $this->outputHandler->advanceProgressBar();
        }

        $this->outputHandler->finishProgressBar();
        $this->setChangedDateTime($currentDateTime);
    }
}
