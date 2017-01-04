<?php

namespace PlentymarketsAdapter\QueryBus\QueryHandler\Category;

use PlentyConnector\Connector\QueryBus\Query\Category\FetchAllCategoriesQuery;
use PlentyConnector\Connector\QueryBus\Query\QueryInterface;
use PlentyConnector\Connector\QueryBus\QueryHandler\QueryHandlerInterface;
use PlentymarketsAdapter\Client\ClientInterface;
use PlentymarketsAdapter\PlentymarketsAdapter;
use PlentymarketsAdapter\ResponseParser\ResponseParserInterface;
use Psr\Log\LoggerInterface;
use UnexpectedValueException;

/**
 * Class FetchAllCategoriesQueryHandler
 */
class FetchAllCategoriesQueryHandler implements QueryHandlerInterface
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
     * FetchAllCategoriesQueryHandler constructor.
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
        return $event instanceof FetchAllCategoriesQuery &&
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
        $elements = $this->client->request('GET', 'categories/');

        $elements = array_filter($elements, function($element) {
            return $element['type'] === 'item';
        });

        array_walk($elements, function($element) {
            // TODO: process elements
        });

        return [];
    }
}
