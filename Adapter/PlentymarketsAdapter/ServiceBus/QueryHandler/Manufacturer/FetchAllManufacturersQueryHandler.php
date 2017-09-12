<?php

namespace PlentymarketsAdapter\ServiceBus\QueryHandler\Manufacturer;

use PlentyConnector\Connector\ServiceBus\Query\Manufacturer\FetchAllManufacturersQuery;
use PlentyConnector\Connector\ServiceBus\Query\QueryInterface;
use PlentyConnector\Connector\ServiceBus\QueryHandler\QueryHandlerInterface;
use PlentyConnector\Console\OutputHandler\OutputHandlerInterface;
use PlentymarketsAdapter\Client\ClientInterface;
use PlentymarketsAdapter\PlentymarketsAdapter;
use PlentymarketsAdapter\ResponseParser\Manufacturer\ManufacturerResponseParserInterface;
use Psr\Log\LoggerInterface;

/**
 * Class FetchAllManufacturersQueryHandler
 */
class FetchAllManufacturersQueryHandler implements QueryHandlerInterface
{
    /**
     * @var ClientInterface
     */
    private $client;

    /**
     * @var ManufacturerResponseParserInterface
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
     * FetchAllManufacturersQueryHandler constructor.
     *
     * @param ClientInterface                     $client
     * @param ManufacturerResponseParserInterface $responseParser
     * @param LoggerInterface                     $logger
     * @param OutputHandlerInterface              $outputHandler
     */
    public function __construct(
        ClientInterface $client,
        ManufacturerResponseParserInterface $responseParser,
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
        return $query instanceof FetchAllManufacturersQuery &&
            $query->getAdapterName() === PlentymarketsAdapter::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(QueryInterface $query)
    {
        $elements = $this->client->getIterator('items/manufacturers');

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
