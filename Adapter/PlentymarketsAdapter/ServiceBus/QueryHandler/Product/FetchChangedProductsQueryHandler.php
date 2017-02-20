<?php

namespace PlentymarketsAdapter\ServiceBus\QueryHandler\Product;

use PlentyConnector\Connector\IdentityService\IdentityServiceInterface;
use PlentyConnector\Connector\ServiceBus\Query\Product\FetchChangedProductsQuery;
use PlentyConnector\Connector\ServiceBus\Query\QueryInterface;
use PlentyConnector\Connector\ServiceBus\QueryHandler\QueryHandlerInterface;
use PlentymarketsAdapter\Client\ClientInterface;
use PlentymarketsAdapter\Helper\LanguageHelper;
use PlentymarketsAdapter\PlentymarketsAdapter;
use PlentymarketsAdapter\ResponseParser\Product\ProductResponseParserInterface;

/**
 * Class FetchChangedProductsQueryHandler.
 */
class FetchChangedProductsQueryHandler implements QueryHandlerInterface
{
    /**
     * @var ClientInterface
     */
    private $client;

    /**
     * @var LanguageHelper
     */
    private $languageHelper;

    /**
     * @var IdentityServiceInterface
     */
    private $identityService;

    /**
     * @var ProductResponseParserInterface
     */
    private $responseParser;

    /**
     * FetchChangedProductsQueryHandler constructor.
     *
     * @param ClientInterface $client
     * @param LanguageHelper $languageHelper
     * @param IdentityServiceInterface $identityService
     * @param ProductResponseParserInterface $responseParser
     */
    public function __construct(
        ClientInterface $client,
        LanguageHelper $languageHelper,
        IdentityServiceInterface $identityService,
        ProductResponseParserInterface $responseParser
    ) {
        $this->client = $client;
        $this->languageHelper = $languageHelper;
        $this->identityService = $identityService;
        $this->responseParser = $responseParser;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(QueryInterface $query)
    {
        return $query instanceof FetchChangedProductsQuery &&
            $query->getAdapterName() === PlentymarketsAdapter::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(QueryInterface $query)
    {
        throw new \Exception('not implemented');
    }
}
