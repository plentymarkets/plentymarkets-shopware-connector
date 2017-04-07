<?php

namespace PlentymarketsAdapter\ServiceBus\QueryHandler\Manufacturer;

use PlentyConnector\Connector\ServiceBus\Query\Manufacturer\FetchAllManufacturersQuery;
use PlentyConnector\Connector\ServiceBus\Query\QueryInterface;
use PlentyConnector\Connector\ServiceBus\QueryHandler\QueryHandlerInterface;
use PlentymarketsAdapter\Client\ClientInterface;
use PlentymarketsAdapter\Helper\MediaCategoryHelper;
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

        foreach ($this->client->getIterator('items/manufacturers') as $element) {
            if (!empty($element['logo'])) {
                $result[] = $media = $this->mediaResponseParser->parse([
                    'mediaCategory' => MediaCategoryHelper::MANUFACTURER,
                    'link' => $element['logo'],
                    'name' => $element['name'],
                    'alternateName' => $element['name'],
                ]);

                $element['logoIdentifier'] = $media->getIdentifier();
            }

            $result[] = $this->manufacturerResponseParser->parse($element);
        }

        return $result;
    }
}
