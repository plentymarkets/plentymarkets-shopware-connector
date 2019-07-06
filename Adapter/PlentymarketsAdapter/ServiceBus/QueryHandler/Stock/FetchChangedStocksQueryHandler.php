<?php

namespace PlentymarketsAdapter\ServiceBus\QueryHandler\Stock;

use Exception;
use PlentymarketsAdapter\Client\ClientInterface;
use PlentymarketsAdapter\Client\Iterator\Iterator;
use PlentymarketsAdapter\PlentymarketsAdapter;
use PlentymarketsAdapter\ResponseParser\Product\Stock\StockResponseParserInterface;
use PlentymarketsAdapter\ServiceBus\ChangedDateTimeTrait;
use Psr\Log\LoggerInterface;
use SystemConnector\Console\OutputHandler\OutputHandlerInterface;
use SystemConnector\ServiceBus\Query\FetchTransferObjectQuery;
use SystemConnector\ServiceBus\Query\QueryInterface;
use SystemConnector\ServiceBus\QueryHandler\QueryHandlerInterface;
use SystemConnector\ServiceBus\QueryType;
use SystemConnector\TransferObject\Product\Stock\Stock;

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
     * @var OutputHandlerInterface
     */
    private $outputHandler;

    public function __construct(
        ClientInterface $client,
        StockResponseParserInterface $responseParser,
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
    public function supports(QueryInterface $query): bool
    {
        return $query instanceof FetchTransferObjectQuery &&
            $query->getAdapterName() === PlentymarketsAdapter::NAME &&
            $query->getObjectType() === Stock::TYPE &&
            $query->getQueryType() === QueryType::CHANGED;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(QueryInterface $query)
    {
        $lastChangedTime = $this->getChangedDateTime();
        $currentDateTime = $this->getCurrentDateTime();

        $stocks = $this->client->getIterator('stockmanagement/stock', [
            'updatedAtFrom' => $lastChangedTime->format(DATE_W3C),
            'updatedAtTo' => $currentDateTime->format(DATE_W3C),
            'columns' => ['variationId'],
        ]);

        $this->outputHandler->startProgressBar(count($stocks));

        foreach ($this->getAffectedVariations($stocks) as $variationIdentifierGroup) {
            if (empty($variationIdentifierGroup)) {
                continue;
            }

            $elements = $this->client->getIterator('items/variations', [
                'with' => 'stock',
                'id' => implode(',', $variationIdentifierGroup),
            ]);

            foreach ($elements as $element) {
                $stock = null;

                try {
                    $stock = $this->responseParser->parse($element);
                } catch (Exception $exception) {
                    $this->logger->error($exception->getMessage());
                }

                if ($stock !== null) {
                    yield $stock;
                }

                $this->outputHandler->advanceProgressBar();
            }
        }

        $this->outputHandler->finishProgressBar();
        $this->setChangedDateTime($currentDateTime);
    }

    private function getAffectedVariations(Iterator $stocks)
    {
        $stockBacklog = [];

        foreach ($stocks as $stock) {
            if (isset($stockBacklog[$stock['variationId']])) {
                continue;
            }

            $stockBacklog[$stock['variationId']] = $stock['variationId'];

            if (count($stockBacklog) % 50 === 0) {
                yield $stockBacklog;

                $stockBacklog = [];
            }
        }

        yield $stockBacklog;
    }
}
