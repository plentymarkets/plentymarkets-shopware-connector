<?php

namespace PlentymarketsAdapter\ServiceBus\QueryHandler\Manufacturer;

use PlentyConnector\Connector\ServiceBus\Query\Manufacturer\FetchChangedManufacturersQuery;
use PlentyConnector\Connector\ServiceBus\Query\QueryInterface;
use PlentyConnector\Connector\ServiceBus\QueryHandler\QueryHandlerInterface;
use PlentymarketsAdapter\Client\ClientInterface;
use PlentymarketsAdapter\PlentymarketsAdapter;
use PlentymarketsAdapter\ResponseParser\Manufacturer\ManufacturerResponseParserInterface;
use PlentymarketsAdapter\ServiceBus\ChangedDateTimeTrait;
use Psr\Log\LoggerInterface;

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
     * @var ManufacturerResponseParserInterface
     */
    private $responseParser;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * FetchChangedManufacturersQueryHandler constructor.
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
        return $query instanceof FetchChangedManufacturersQuery &&
            $query->getAdapterName() === PlentymarketsAdapter::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(QueryInterface $query)
    {
        $lastCangedTime = $this->getChangedDateTime();
        $currentDateTime = $this->getCurrentDateTime();

        $manufacturers = $this->client->getIterator('items/manufacturers', [
            'updatedAt' => $lastCangedTime->format(DATE_W3C),
        ]);

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

        $this->setChangedDateTime($currentDateTime);

        return $parsedElements;
    }
}
