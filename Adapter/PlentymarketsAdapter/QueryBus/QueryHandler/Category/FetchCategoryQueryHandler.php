<?php

namespace PlentymarketsAdapter\QueryBus\QueryHandler\Category;

use PlentyConnector\Connector\IdentityService\IdentityServiceInterface;
use PlentyConnector\Connector\QueryBus\Query\Category\FetchCategoryQuery;
use PlentyConnector\Connector\QueryBus\Query\QueryInterface;
use PlentyConnector\Connector\QueryBus\QueryHandler\QueryHandlerInterface;
use PlentyConnector\Connector\TransferObject\TransferObjectInterface;
use PlentymarketsAdapter\Client\ClientInterface;
use PlentymarketsAdapter\PlentymarketsAdapter;
use PlentymarketsAdapter\ResponseParser\ResponseParserInterface;

/**
 * Class FetchCategoryQueryHandler
 */
class FetchCategoryQueryHandler implements QueryHandlerInterface
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
     * @var IdentityServiceInterface
     */
    private $identityService;

    /**
     * FetchCategoryQueryHandler constructor.
     *
     * @param ClientInterface $client
     * @param ResponseParserInterface $responseMapper
     * @param IdentityServiceInterface $identityService
     */
    public function __construct(
        ClientInterface $client,
        ResponseParserInterface $responseMapper,
        IdentityServiceInterface $identityService
    ) {
        $this->client = $client;
        $this->responseMapper = $responseMapper;
        $this->identityService = $identityService;
    }

    /**
     * @param QueryInterface $event
     *
     * @return bool
     */
    public function supports(QueryInterface $event)
    {
        return $event instanceof FetchCategoryQuery &&
            $event->getAdapterName() === PlentymarketsAdapter::getName();
    }

    /**
     * @param QueryInterface $event
     *
     * @return TransferObjectInterface
     */
    public function handle(QueryInterface $event)
    {
        // TODO: process elements
    }
}
