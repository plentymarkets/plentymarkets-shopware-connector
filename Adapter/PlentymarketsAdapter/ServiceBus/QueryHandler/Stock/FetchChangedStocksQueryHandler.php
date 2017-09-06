<?php

namespace PlentymarketsAdapter\ServiceBus\QueryHandler\Stock;

use PlentyConnector\Connector\ServiceBus\Query\QueryInterface;
use PlentyConnector\Connector\ServiceBus\Query\Stock\FetchChangedStocksQuery;
use PlentyConnector\Connector\ServiceBus\QueryHandler\QueryHandlerInterface;
use PlentymarketsAdapter\Client\ClientInterface;
use PlentymarketsAdapter\PlentymarketsAdapter;
use PlentymarketsAdapter\ResponseParser\Product\Stock\StockResponseParserInterface;
use PlentymarketsAdapter\ServiceBus\ChangedDateTimeTrait;

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
     * FetchChangedStocksQueryHandler constructor.
     *
     * @param ClientInterface              $client
     * @param StockResponseParserInterface $responseParser
     */
    public function __construct(ClientInterface $client, StockResponseParserInterface $responseParser)
    {
        $this->client = $client;
        $this->responseParser = $responseParser;
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

        foreach ($variations as $variation) {
            $transferObjects = $this->responseParser->parse($variation);

            foreach ($transferObjects as $object) {
                yield $object;
            }
        }

        $this->setChangedDateTime($currentDateTime);
    }
}
