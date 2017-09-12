<?php

namespace PlentymarketsAdapter\ServiceBus\QueryHandler\Manufacturer;

use PlentyConnector\Connector\ServiceBus\Query\Manufacturer\FetchAllManufacturersQuery;
use PlentyConnector\Connector\ServiceBus\Query\QueryInterface;
use PlentyConnector\Connector\ServiceBus\QueryHandler\QueryHandlerInterface;
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
     * FetchAllManufacturersQueryHandler constructor.
     *
     * @param ClientInterface $client
     * @param ManufacturerResponseParserInterface $responseParser
     * @param LoggerInterface $logger
     */
    public function __construct(
        ClientInterface $client,
        ManufacturerResponseParserInterface $responseParser,
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
        return $query instanceof FetchAllManufacturersQuery &&
            $query->getAdapterName() === PlentymarketsAdapter::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(QueryInterface $query)
    {
        $manufacturers = $this->client->getIterator('items/manufacturers');

        $parsedElements = [];
        foreach ($manufacturers as $manufacturer) {
            try {
                $result = $this->responseParser->parse($manufacturer);
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
