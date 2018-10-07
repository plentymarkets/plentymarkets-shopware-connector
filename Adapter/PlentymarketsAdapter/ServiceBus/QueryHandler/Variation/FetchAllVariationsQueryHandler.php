<?php

namespace PlentymarketsAdapter\ServiceBus\QueryHandler\Variation;

use Exception;
use PlentyConnector\Connector\Console\OutputHandler\OutputHandlerInterface;
use PlentyConnector\Connector\ServiceBus\Query\FetchTransferObjectQuery;
use PlentyConnector\Connector\ServiceBus\Query\QueryInterface;
use PlentyConnector\Connector\ServiceBus\QueryHandler\QueryHandlerInterface;
use PlentyConnector\Connector\ServiceBus\QueryType;
use PlentyConnector\Connector\TransferObject\Product\Variation\Variation;
use PlentymarketsAdapter\PlentymarketsAdapter;
use PlentymarketsAdapter\ReadApi\Item;
use PlentymarketsAdapter\ResponseParser\Product\Variation\VariationResponseParserInterface;
use Psr\Log\LoggerInterface;

class FetchAllVariationsQueryHandler implements QueryHandlerInterface
{
    /**
     * @var Item
     */
    private $itemApi;

    /**
     * @var VariationResponseParserInterface
     */
    private $responseParser;

    /**
     * @var OutputHandlerInterface
     */
    private $outputHandler;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        Item $itemApi,
        VariationResponseParserInterface $responseParser,
        OutputHandlerInterface $outputHandler,
        LoggerInterface $logger
    ) {
        $this->itemApi = $itemApi;
        $this->responseParser = $responseParser;
        $this->outputHandler = $outputHandler;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(QueryInterface $query)
    {
        return $query instanceof FetchTransferObjectQuery &&
            $query->getAdapterName() === PlentymarketsAdapter::NAME &&
            $query->getObjectType() === Variation::TYPE &&
            $query->getQueryType() === QueryType::ALL;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(QueryInterface $query)
    {
        $elements = $this->itemApi->findAll();

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
    }
}
