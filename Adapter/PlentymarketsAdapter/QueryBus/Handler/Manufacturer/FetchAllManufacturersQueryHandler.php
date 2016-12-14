<?php

namespace PlentyConnector\Adapter\PlentymarketsAdapter\QueryBus\Handler\Manufacturer;

use PlentyConnector\Connector\QueryBus\Handler\QueryHandlerInterface;
use PlentyConnector\Connector\QueryBus\Query\Manufacturer\FetchAllManufacturersQuery;
use PlentyConnector\Connector\QueryBus\Query\QueryInterface;
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
     * @param QueryInterface $event
     *
     * @return bool
     */
    public function supports(QueryInterface $event)
    {
        return $event instanceof FetchAllManufacturersQuery &&
            $event->getAdapterName() === PlentymarketsAdapter::getName();
    }

    /**
     * @param QueryInterface $event
     *
     * @return TransferObjectInterface[]
     *
     * @throws UnexpectedValueException
     */
    public function handle(QueryInterface $event)
    {
        $result = [];
        foreach ($this->client->getIterator('items/manufacturers') as $element) {
            try {
                $result[] = $this->responseMapper->parse($element);
            } catch (Exception $e) {
                $this->logger->error($e->getMessage());
            }
        }

        return $result;
    }
}
