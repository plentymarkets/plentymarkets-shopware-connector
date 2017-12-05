<?php

namespace PlentymarketsAdapter\ServiceBus\QueryHandler\Stock;

use Exception;
use PlentyConnector\Connector\ServiceBus\Query\FetchTransferObjectQuery;
use PlentyConnector\Connector\ServiceBus\Query\QueryInterface;
use PlentyConnector\Connector\ServiceBus\QueryHandler\QueryHandlerInterface;
use PlentyConnector\Connector\ServiceBus\QueryType;
use PlentyConnector\Connector\TransferObject\Product\Stock\Stock;
use PlentyConnector\Console\OutputHandler\OutputHandlerInterface;
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
     * @var OutputHandlerInterface
     */
    private $outputHandler;

    /**
     * FetchChangedStocksQueryHandler constructor.
     *
     * @param ClientInterface              $client
     * @param StockResponseParserInterface $responseParser
     * @param LoggerInterface              $logger
     * @param OutputHandlerInterface       $outputHandler
     */
    public function __construct(
        ClientInterface $client,
        StockResponseParserInterface $responseParser,
        LoggerInterface $logger,
        OutputHandlerInterface $outputHandler
    ) {
        $this->client         = $client;
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
            $query->getObjectType() === Stock::TYPE &&
            $query->getQueryType() === QueryType::CHANGED;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(QueryInterface $query)
    {
        $lastCangedTime  = $this->getChangedDateTime();
        $currentDateTime = $this->getCurrentDateTime();

        $stocks = $this->client->getIterator('stockmanagement/stock', [
            'updatedAtFrom' => $lastCangedTime->format(DATE_W3C),
            'updatedAtTo'   => $currentDateTime->format(DATE_W3C),
            'columns'       => ['variationId'],
        ]);

        $variationIdentifiers = [];
        foreach ($stocks as $stock) {
            $variationIdentifiers[$stock['variationId']] = $stock['variationId'];

            unset($stock);
        }

        $this->outputHandler->startProgressBar(count($variationIdentifiers));

        $variationIdentifierGroups = array_chunk($variationIdentifiers, 50);
        foreach ($variationIdentifierGroups as $variationIdentifierGroup) {
            $elements = $this->client->getIterator('items/variations', [
                'with' => 'stock',
                'id'   => implode(',', $variationIdentifierGroup),
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
}
