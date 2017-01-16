<?php

namespace PlentymarketsAdapter\QueryBus\QueryHandler\Category;

use PlentyConnector\Connector\ConfigService\ConfigServiceInterface;
use PlentyConnector\Connector\QueryBus\Query\Category\FetchChangedCategoriesQuery;
use PlentyConnector\Connector\QueryBus\Query\QueryInterface;
use PlentyConnector\Connector\QueryBus\QueryHandler\QueryHandlerInterface;
use PlentymarketsAdapter\Client\ClientInterface;
use PlentymarketsAdapter\PlentymarketsAdapter;
use PlentymarketsAdapter\QueryBus\ChangedDateTimeTrait;
use PlentymarketsAdapter\ResponseParser\ResponseParserInterface;
use Psr\Log\LoggerInterface;

/**
 * Class FetchChangedCategoriesQueryHandler.
 */
class FetchChangedCategoriesQueryHandler implements QueryHandlerInterface
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
    private $responseMapper;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * FetchChangedCategoriesQueryHandler constructor.
     *
     * @param ClientInterface $client
     * @param ConfigServiceInterface $config
     * @param ResponseParserInterface $responseMapper
     * @param LoggerInterface $logger
     */
    public function __construct(
        ClientInterface $client,
        ConfigServiceInterface $config,
        ResponseParserInterface $responseMapper,
        LoggerInterface $logger
    ) {
        $this->client = $client;
        $this->config = $config;
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
        return $query instanceof FetchChangedCategoriesQuery &&
            $query->getAdapterName() === PlentymarketsAdapter::NAME;
    }

    /**
     * @param QueryInterface $query
     *
     * @return TransferObjectInterface[]
     */
    public function handle(QueryInterface $query)
    {
        // TODO: process elements
    }
}
