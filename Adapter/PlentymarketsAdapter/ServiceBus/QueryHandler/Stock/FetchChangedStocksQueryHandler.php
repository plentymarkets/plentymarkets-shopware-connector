<?php

namespace PlentymarketsAdapter\ServiceBus\QueryHandler\Stock;

use Exception;
use PlentyConnector\Connector\Console\OutputHandler\OutputHandlerInterface;
use PlentyConnector\Connector\ServiceBus\Query\FetchTransferObjectQuery;
use PlentyConnector\Connector\ServiceBus\Query\QueryInterface;
use PlentyConnector\Connector\ServiceBus\QueryHandler\QueryHandlerInterface;
use PlentyConnector\Connector\ServiceBus\QueryType;
use PlentyConnector\Connector\TransferObject\Product\Stock\Stock;
use PlentymarketsAdapter\Client\ClientInterface;
use PlentymarketsAdapter\Client\Iterator\Iterator;
use PlentymarketsAdapter\PlentymarketsAdapter;
use PlentymarketsAdapter\ResponseParser\Product\Stock\StockResponseParserInterface;
use PlentymarketsAdapter\ServiceBus\ChangedDateTimeTrait;
use Psr\Log\LoggerInterface;

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
    public function supports(QueryInterface $query)
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
        $lastCangedTime = $this->getChangedDateTime();
        $currentDateTime = $this->getCurrentDateTime();

        $stocks = $this->client->getIterator('stockmanagement/stock', [
            'updatedAtFrom' => $lastCangedTime->format(DATE_W3C),
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
