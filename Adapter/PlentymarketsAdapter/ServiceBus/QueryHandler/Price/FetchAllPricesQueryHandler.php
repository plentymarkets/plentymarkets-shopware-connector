<?php

namespace PlentymarketsAdapter\ServiceBus\QueryHandler\Price;

use Exception;
use PlentymarketsAdapter\Client\ClientInterface;
use PlentymarketsAdapter\PlentymarketsAdapter;
use PlentymarketsAdapter\ResponseParser\Product\Price\PriceResponseParserInterface;
use Psr\Log\LoggerInterface;
use SystemConnector\Console\OutputHandler\OutputHandlerInterface;
use SystemConnector\ServiceBus\Query\FetchTransferObjectQuery;
use SystemConnector\ServiceBus\Query\QueryInterface;
use SystemConnector\ServiceBus\QueryHandler\QueryHandlerInterface;
use SystemConnector\ServiceBus\QueryType;
use SystemConnector\TransferObject\Product\Price\Price;

class FetchAllPricesQueryHandler implements QueryHandlerInterface
{
    /**
     * @var ClientInterface
     */
    private $client;

    /**
     * @var PriceResponseParserInterface
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

    public function __construct(
        ClientInterface $client,
        PriceResponseParserInterface $responseParser,
        LoggerInterface $logger,
        OutputHandlerInterface $outputHandler
    ) {
        $this->client = $client;
        $this->responseParser = $responseParser;
        $this->logger = $logger;
        $this->outputHandler = $outputHandler;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(QueryInterface $query)
    {
        return $query instanceof FetchTransferObjectQuery &&
            $query->getAdapterName() === PlentymarketsAdapter::NAME &&
            $query->getObjectType() === Price::TYPE &&
            $query->getQueryType() === QueryType::ALL;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(QueryInterface $query)
    {
        $elements = $this->client->getIterator('/rest/items/variations/variation_sales_prices');

        $this->outputHandler->startProgressBar(count($elements));

        foreach ($elements as $element) {
            $price = null;

            try {
                $price = $this->responseParser->parse($element);
            } catch (Exception $exception) {
                $this->logger->error($exception->getMessage());
            }

            if ($price !== null) {
                yield $price;
            }

            $this->outputHandler->advanceProgressBar();
        }

        $this->outputHandler->finishProgressBar();
    }
}
