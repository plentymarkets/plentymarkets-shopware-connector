<?php

namespace PlentymarketsAdapter\QueryBus\QueryHandler\Manufacturer;

use Exception;
use PlentyConnector\Connector\ConfigService\ConfigServiceInterface;
use PlentyConnector\Connector\QueryBus\Query\Manufacturer\FetchChangedManufacturersQuery;
use PlentyConnector\Connector\QueryBus\Query\QueryInterface;
use PlentyConnector\Connector\QueryBus\QueryHandler\QueryHandlerInterface;
use PlentymarketsAdapter\Client\ClientInterface;
use PlentymarketsAdapter\PlentymarketsAdapter;
use PlentymarketsAdapter\QueryBus\ChangedDateTimeTrait;
use PlentymarketsAdapter\ResponseParser\ResponseParserInterface;
use Psr\Log\LoggerInterface;
use UnexpectedValueException;

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
     * @var ResponseParserInterface
     */
    private $manufacturerResponseParser;

    /**
     * @var ResponseParserInterface
     */
    private $mediaResponseParser;

    /**
     * FetchChangedManufacturersQueryHandler constructor.
     *
     * @param ClientInterface $client
     * @param ConfigServiceInterface $config
     * @param ResponseParserInterface $manufacturerResponseParser
     * @param ResponseParserInterface $mediaResponseParser
     */
    public function __construct(
        ClientInterface $client,
        ConfigServiceInterface $config,
        ResponseParserInterface $manufacturerResponseParser,
        ResponseParserInterface $mediaResponseParser
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
                    'name' => $element['name']
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
