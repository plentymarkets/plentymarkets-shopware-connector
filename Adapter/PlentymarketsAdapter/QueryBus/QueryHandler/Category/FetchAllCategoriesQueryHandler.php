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
     * @param QueryInterface $query
     *
     * @return bool
     */
    public function supports(QueryInterface $query)
    {
        return $query instanceof FetchAllCategoriesQuery &&
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
        $elements = $this->client->request('GET', 'categories', [
            'with' => 'clients',
        ]);

        $elements = array_filter($elements, function ($element) {
            return $element['type'] === 'item' && $element['right'] === 'all';
        });

        array_walk($elements, function ($element) {
            // TODO: process elements
        });

        return [];
    }
}
