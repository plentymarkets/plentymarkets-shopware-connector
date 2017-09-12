<?php

namespace PlentymarketsAdapter\ServiceBus\QueryHandler\Stock;

use PlentyConnector\Connector\ServiceBus\Query\QueryInterface;
use PlentyConnector\Connector\ServiceBus\Query\Stock\FetchAllStocksQuery;
use PlentyConnector\Connector\ServiceBus\QueryHandler\QueryHandlerInterface;
use PlentymarketsAdapter\Client\ClientInterface;
use PlentymarketsAdapter\PlentymarketsAdapter;
use PlentymarketsAdapter\ResponseParser\Product\Stock\StockResponseParserInterface;
use Psr\Log\LoggerInterface;

/**
 * Class FetchAllStocksQueryHandler
 */
class FetchAllStocksQueryHandler implements QueryHandlerInterface
{
    /**
     * @var ClientInterface
     */
    private $client;

    /**
     * @var StockResponseParserInterface
     */
    private $responseParser;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * FetchAllStocksQueryHandler constructor.
     *
     * @param ClientInterface $client
     * @param StockResponseParserInterface $responseParser
     * @param LoggerInterface $logger
     */
    public function __construct(
        ClientInterface $client,
        StockResponseParserInterface $responseParser,
        LoggerInterface $logger
    ) {
        $this->client = $client;
        $this->responseParser = $responseParser;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(QueryInterface $query)
    {
        return $query instanceof FetchAllStocksQuery &&
            $query->getAdapterName() === PlentymarketsAdapter::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(QueryInterface $query)
    {
        $stocks = $this->client->getIterator('stockmanagement/stock');

        $groupedStock = [];
        foreach ($stocks as $stock) {
            $groupedStock[$stock['variationId']]['id'] = $stock['variationId'];
            $groupedStock[$stock['variationId']]['stock'][] = $stock;
        }

        $parsedElements = [];
        foreach ($groupedStock as $variation) {
            try {
                $result = $this->responseParser->parse($variation);
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
