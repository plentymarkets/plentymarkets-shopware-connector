<?php

namespace PlentymarketsAdapter\ServiceBus\QueryHandler\Manufacturer;

use PlentyConnector\Connector\ConfigService\ConfigServiceInterface;
use PlentyConnector\Connector\ServiceBus\Query\Manufacturer\FetchChangedManufacturersQuery;
use PlentyConnector\Connector\ServiceBus\Query\QueryInterface;
use PlentyConnector\Connector\ServiceBus\QueryHandler\QueryHandlerInterface;
use PlentymarketsAdapter\Client\ClientInterface;
use PlentymarketsAdapter\PlentymarketsAdapter;
use PlentymarketsAdapter\ResponseParser\Manufacturer\ManufacturerResponseParserInterface;
use PlentymarketsAdapter\ResponseParser\Media\MediaResponseParserInterface;
use PlentymarketsAdapter\ServiceBus\ChangedDateTimeTrait;

/**
 * Class FetchChangedManufacturersQueryHandler.
 */
class FetchChangedManufacturersQueryHandler implements QueryHandlerInterface
{
    use ChangedDateTimeTrait;

    /**
     * @var ClientInterface
     */
    private $client;

    /**
     * @var ConfigServiceInterface
     */
    private $config;

    /**
     * @var ManufacturerResponseParserInterface
     */
    private $manufacturerResponseParser;

    /**
     * @var MediaResponseParserInterface
     */
    private $mediaResponseParser;

    /**
     * FetchChangedManufacturersQueryHandler constructor.
     *
     * @param ClientInterface $client
     * @param ConfigServiceInterface $config
     * @param ManufacturerResponseParserInterface $manufacturerResponseParser
     * @param MediaResponseParserInterface $mediaResponseParser
     */
    public function __construct(
        ClientInterface $client,
        ConfigServiceInterface $config,
        ManufacturerResponseParserInterface $manufacturerResponseParser,
        MediaResponseParserInterface $mediaResponseParser
    ) {
        $this->client = $client;
        $this->config = $config;
        $this->manufacturerResponseParser = $manufacturerResponseParser;
        $this->mediaResponseParser = $mediaResponseParser;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(QueryInterface $query)
    {
        return $query instanceof FetchChangedManufacturersQuery &&
            $query->getAdapterName() === PlentymarketsAdapter::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(QueryInterface $query)
    {
        $criteria = [
            'lastUpdateTimestamp' => $this->getChangedDateTime($this->config),
        ];

        $result = [];

        foreach ($this->client->getIterator('items/manufacturers', $criteria) as $element) {
            if (!empty($element['logo'])) {
                $result[] = $media = $this->mediaResponseParser->parse([
                    'link' => $element['logo'],
                    'name' => $element['name'],
                ]);

                $element['logoIdentifier'] = $media->getIdentifier();
            }

            $result[] = $this->manufacturerResponseParser->parse($element);
        }

        if (!empty($result)) {
            $this->setChangedDateTime($this->config);
        }

        return $result;
    }
}
