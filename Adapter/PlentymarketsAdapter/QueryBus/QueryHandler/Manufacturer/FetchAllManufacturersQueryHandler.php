<?php

namespace PlentymarketsAdapter\QueryBus\QueryHandler\Manufacturer;

use PlentyConnector\Connector\QueryBus\Query\Manufacturer\FetchAllManufacturersQuery;
use PlentyConnector\Connector\QueryBus\Query\QueryInterface;
use PlentyConnector\Connector\QueryBus\QueryHandler\QueryHandlerInterface;
use PlentymarketsAdapter\Client\ClientInterface;
use PlentymarketsAdapter\PlentymarketsAdapter;
use PlentymarketsAdapter\ResponseParser\ResponseParserInterface;
use Psr\Log\LoggerInterface;
use UnexpectedValueException;

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
     * @var ResponseParserInterface
     */
    private $manufacturerResponseParser;

    /**
     * @var ResponseParserInterface
     */
    private $mediaResponseParser;

    /**
     * FetchAllManufacturersQueryHandler constructor.
     *
     * @param ClientInterface $client
     * @param ResponseParserInterface $manufacturerResponseParser
     * @param ResponseParserInterface $mediaResponseParser
     */
    public function __construct(
        ClientInterface $client,
        ResponseParserInterface $manufacturerResponseParser,
        ResponseParserInterface $mediaResponseParser
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
                    'link' => $element['logo'],
                    'name' => $element['name']
                ]);

                $element['logoIdentifier'] = $media->getIdentifier();
            }

            $result[] = $this->manufacturerResponseParser->parse($element);
        }

        return $result;
    }
}
