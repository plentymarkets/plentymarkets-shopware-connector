<?php

namespace PlentymarketsAdapter\ServiceBus\QueryHandler\Stock;

use PlentyConnector\Connector\ServiceBus\Query\QueryInterface;
use PlentyConnector\Connector\ServiceBus\Query\Stock\FetchChangedStocksQuery;
use PlentyConnector\Connector\ServiceBus\QueryHandler\QueryHandlerInterface;
use PlentymarketsAdapter\Client\ClientInterface;
use PlentymarketsAdapter\PlentymarketsAdapter;
use PlentymarketsAdapter\ResponseParser\Product\Stock\StockResponseParserInterface;
use PlentymarketsAdapter\ServiceBus\ChangedDateTimeTrait;
use Psr\Log\LoggerInterface;

/**
 * Class FetchChangedStocksQueryHandler.
 */
class FetchChangedStocksQueryHandler implements QueryHandlerInterface
{
    use ChangedDateTimeTrait;

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
     * FetchChangedStocksQueryHandler constructor.
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
        return $query instanceof FetchChangedStocksQuery &&
            $query->getAdapterName() === PlentymarketsAdapter::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(QueryInterface $query)
    {
        $lastCangedTime = $this->getChangedDateTime();
        $currentDateTime = $this->getCurrentDateTime();

        $stocks = $this->client->getIterator('stockmanagement/stock', [
            'updatedAtFrom' => $lastCangedTime->format(DATE_W3C),
            'updatedAtTo' => $currentDateTime->format(DATE_W3C),
        ]);

        $variationIdentifiers = [];
        foreach ($stocks as $stock) {
            $variationIdentifiers[$stock['variationId']] = $stock['variationId'];
        }

        if (empty($variationIdentifiers)) {
            return [];
        }

        $variations = $this->client->getIterator('items/variations', [
            'with' => 'stock',
            'id' => implode(',', $variationIdentifiers),
        ]);

        $parsedElements = [];
        foreach ($variations as $variation) {
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

        $this->setChangedDateTime($currentDateTime);

        return $parsedElements;
    }
}
