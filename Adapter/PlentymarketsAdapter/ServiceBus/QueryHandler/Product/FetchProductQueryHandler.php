<?php

namespace PlentymarketsAdapter\ServiceBus\QueryHandler\Product;

use PlentyConnector\Connector\IdentityService\IdentityServiceInterface;
use PlentyConnector\Connector\ServiceBus\Query\FetchQueryInterface;
use PlentyConnector\Connector\ServiceBus\Query\Product\FetchProductQuery;
use PlentyConnector\Connector\ServiceBus\Query\QueryInterface;
use PlentyConnector\Connector\ServiceBus\QueryHandler\QueryHandlerInterface;
use PlentyConnector\Connector\TransferObject\Product\Product;
use PlentymarketsAdapter\Client\ClientInterface;
use PlentymarketsAdapter\Helper\LanguageHelper;
use PlentymarketsAdapter\PlentymarketsAdapter;
use PlentymarketsAdapter\ResponseParser\Product\ProductResponseParserInterface;

/**
 * Class FetchProductQueryHandler.
 */
class FetchProductQueryHandler implements QueryHandlerInterface
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
     * FetchProductQueryHandler constructor.
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
        return $query instanceof FetchProductQuery &&
            $query->getAdapterName() === PlentymarketsAdapter::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(QueryInterface $query)
    {
        /**
         * @var FetchQueryInterface $query
         */
        $identity = $this->identityService->findOneBy([
            'objectIdentifier' => $query->getIdentifier(),
            'objectType' => Product::TYPE,
            'adapterName' => PlentymarketsAdapter::NAME,
        ]);

        $product = $this->client->request('GET', 'items/' . $identity->getAdapterIdentifier(), [
            'lang' => $this->languageHelper->getLanguagesQueryString(),
        ]);

        $result = [];

        $result[] = $this->responseParser->parse($product, $result);

        return array_filter($result);
    }
}
