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
    private $responseMapper;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * FetchAllManufacturersQueryHandler constructor.
     *
     * @param ClientInterface $client
     * @param ResponseParserInterface $responseMapper
     * @param LoggerInterface $logger
     */
    public function __construct(
        ClientInterface $client,
        ResponseParserInterface $responseMapper,
        LoggerInterface $logger
    ) {
        $this->client = $client;
        $this->responseMapper = $responseMapper;
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
                $result[] = $this->responseMapper->parse($element);
            } catch (Exception $exception) {
                $this->logger->error($exception->getMessage());
            }
        }

        return $result;
    }
}
