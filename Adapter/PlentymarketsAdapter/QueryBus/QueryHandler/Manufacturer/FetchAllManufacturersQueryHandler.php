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
     * @var LoggerInterface
     */
    private $logger;

    /**
     * FetchAllManufacturersQueryHandler constructor.
     *
     * @param ClientInterface $client
     * @param ResponseParserInterface $manufacturerResponseParser
     * @param ResponseParserInterface $mediaResponseParser
     * @param LoggerInterface $logger
     */
    public function __construct(
        ClientInterface $client,
        ResponseParserInterface $manufacturerResponseParser,
        ResponseParserInterface $mediaResponseParser,
        LoggerInterface $logger
    ) {
        $this->client = $client;
        $this->manufacturerResponseParser = $manufacturerResponseParser;
        $this->mediaResponseParser = $mediaResponseParser;
        $this->logger = $logger;
    }

    /**
     * @param QueryInterface $query
     *
     * @return bool
     */
    public function supports(QueryInterface $query)
    {
        return $query instanceof FetchAllManufacturersQuery &&
            $query->getAdapterName() === PlentymarketsAdapter::NAME;
    }

    /**
     * @param QueryInterface $query
     *
     * @return TransferObjectInterface[]
     *
     * @throws UnexpectedValueException
     */
    public function handle(QueryInterface $query)
    {
        $result = [];

        foreach ($this->client->getIterator('items/manufacturers') as $element) {
            try {
                if (!empty($element['logo'])) {
                    $result[] = $media = $this->mediaResponseParser->parse([
                        'link' => $element['logo'],
                        'name' => $element['name']
                    ]);

                    $element['logoIdentifier'] = $media->getIdentifier();
                }

                $result[] = $this->manufacturerResponseParser->parse($element);
            } catch (Exception $exception) {
                $this->logger->error($exception->getMessage());
            }
        }

        return $result;
    }
}
