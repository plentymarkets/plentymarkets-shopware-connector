<?php

namespace PlentymarketsAdapter\ServiceBus\QueryHandler\Manufacturer;

use PlentyConnector\Connector\ServiceBus\Query\Manufacturer\FetchAllManufacturersQuery;
use PlentyConnector\Connector\ServiceBus\Query\QueryInterface;
use PlentyConnector\Connector\ServiceBus\QueryHandler\QueryHandlerInterface;
use PlentymarketsAdapter\Client\ClientInterface;
use PlentymarketsAdapter\PlentymarketsAdapter;
use PlentymarketsAdapter\ResponseParser\Manufacturer\ManufacturerResponseParserInterface;
use PlentymarketsAdapter\ResponseParser\Media\MediaResponseParserInterface;

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
    private $manufacturerResponseParser;

    /**
     * @var MediaResponseParserInterface
     */
    private $mediaResponseParser;

    /**
     * FetchAllManufacturersQueryHandler constructor.
     *
     * @param ClientInterface                     $client
     * @param ManufacturerResponseParserInterface $manufacturerResponseParser
     * @param MediaResponseParserInterface        $mediaResponseParser
     */
    public function __construct(
        ClientInterface $client,
        ManufacturerResponseParserInterface $manufacturerResponseParser,
        MediaResponseParserInterface $mediaResponseParser
    ) {
        $this->client = $client;
        $this->manufacturerResponseParser = $manufacturerResponseParser;
        $this->mediaResponseParser = $mediaResponseParser;
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
        $result = [];

        $manufacturers = $this->client->getIterator('items/manufacturers');

        foreach ($manufacturers as $element) {
            $result = $this->manufacturerResponseParser->parse($element);

            if (empty($result)) {
                continue;
            }

            $parsedElements = array_filter($result);

            foreach ($parsedElements as $parsedElement) {
                yield $parsedElement;
            }
        }

        return $result;
    }
}
