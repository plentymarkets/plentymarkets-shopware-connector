<?php

namespace PlentymarketsAdapter\ServiceBus\QueryHandler\Product;

use PlentyConnector\Connector\ServiceBus\Query\Product\FetchAllProductsQuery;
use PlentyConnector\Connector\ServiceBus\Query\QueryInterface;
use PlentyConnector\Connector\ServiceBus\QueryHandler\QueryHandlerInterface;
use PlentymarketsAdapter\Client\ClientInterface;
use PlentymarketsAdapter\Helper\LanguageHelper;
use PlentymarketsAdapter\PlentymarketsAdapter;
use PlentymarketsAdapter\ResponseParser\Product\ProductResponseParserInterface;

/**
 * Class FetchAllProductsQueryHandler.
 */
class FetchAllProductsQueryHandler implements QueryHandlerInterface
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
     * @var ProductResponseParserInterface
     */
    private $responseParser;

    /**
     * FetchAllProductsQueryHandler constructor.
     *
     * @param ClientInterface $client
     * @param LanguageHelper $languageHelper
     * @param ProductResponseParserInterface $responseParser
     */
    public function __construct(
        ClientInterface $client,
        LanguageHelper $languageHelper,
        ProductResponseParserInterface $responseParser
    ) {
        $this->client = $client;
        $this->languageHelper = $languageHelper;
        $this->responseParser = $responseParser;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(QueryInterface $query)
    {
        return $query instanceof FetchAllProductsQuery &&
            $query->getAdapterName() === PlentymarketsAdapter::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(QueryInterface $query)
    {
        $products = $this->client->request('GET', 'items', [
            'lang' => $this->languageHelper->getLanguagesQueryString(),
        ]);

        $products = array_filter($products, function (array $product) {
            return $product['id'] === 132;
        });

        $result = [];

        foreach ($products as $product) {
            $result[] = $this->responseParser->parse($product, $result);
        }

        return $result;
    }
}
