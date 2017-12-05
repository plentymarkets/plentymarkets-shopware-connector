<?php

namespace PlentymarketsAdapter\ServiceBus\QueryHandler\Manufacturer;

use Exception;
use PlentyConnector\Connector\ServiceBus\Query\FetchTransferObjectQuery;
use PlentyConnector\Connector\ServiceBus\Query\QueryInterface;
use PlentyConnector\Connector\ServiceBus\QueryHandler\QueryHandlerInterface;
use PlentyConnector\Connector\ServiceBus\QueryType;
use PlentyConnector\Connector\TransferObject\Manufacturer\Manufacturer;
use PlentyConnector\Console\OutputHandler\OutputHandlerInterface;
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
     * @var OutputHandlerInterface
     */
    private $outputHandler;

    /**
     * FetchChangedManufacturersQueryHandler constructor.
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
            $query->getObjectType() === Manufacturer::TYPE &&
            $query->getQueryType() === QueryType::CHANGED;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(QueryInterface $query)
    {
        $lastCangedTime  = $this->getChangedDateTime();
        $currentDateTime = $this->getCurrentDateTime();

        $elements = $this->client->getIterator('items/manufacturers', [
            'updatedAt' => $lastCangedTime->format(DATE_W3C),
        ]);

        $this->outputHandler->startProgressBar(count($elements));

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

        $this->outputHandler->finishProgressBar();
        $this->setChangedDateTime($currentDateTime);
    }
}
