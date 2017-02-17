<?php

namespace PlentymarketsAdapter\ServiceBus\QueryHandler\Product;

use PlentyConnector\Connector\IdentityService\IdentityServiceInterface;
use PlentyConnector\Connector\ServiceBus\Query\Product\FetchAllProductsQuery;
use PlentyConnector\Connector\ServiceBus\Query\QueryInterface;
use PlentyConnector\Connector\ServiceBus\QueryHandler\QueryHandlerInterface;
use PlentyConnector\Connector\TransferObject\Product\Product;
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
     * @var IdentityServiceInterface
     */
    private $identityService;

    /**
     * @var ProductResponseParserInterface
     */
    private $responseParser;

    /**
     * FetchAllProductsQueryHandler constructor.
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
        return $query instanceof FetchAllProductsQuery &&
            $query->getAdapterName() === PlentymarketsAdapter::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(QueryInterface $query)
    {
        $webstores = $this->client->request('GET', 'webstores');

        $iterator = $this->client->getIterator('items', [
            'lang' => implode(',', array_column($this->languageHelper->getLanguages(), 'id')),
        ]);

        $result = [];

        foreach ($iterator as $product) {
            $variations = $this->client->request('GET', 'items/' . $product['id'] . '/variations', [
                'with' => 'variationSuppliers,variationClients,variationSalesPrices,variationCategories,variationDefaultCategory,unit,variationAttributeValues',
            ]);

            $mainVariation = $this->responseParser->getMainVariation($variations);

            $identity = $this->identityService->findOneOrCreate(
                (string) $product['id'],
                PlentymarketsAdapter::NAME,
                Product::TYPE
            );

            // TODO: get texts from variations: items/{id}/variations/{variationId}/descriptions
            /* TODO:
                post: rest/items/properties/{id}/selections
                get: rest/items/properties/{id}/selections (Gibt alle Werte für die Property ID aus)
                get: rest/items/properties/{id}/selections/{lang} (Gibt einen Wert für die Property ID in der entspr. Sprache aus)
                put: rest/items/properties/{id}/selections/{lang}
                delete: rest/items/properties/{id}/selections/{lang}
            */
            $object = Product::fromArray([
                'identifier' => $identity->getObjectIdentifier(),
                'name' => $product['texts'][0]['name1'],
                'active' => $product['isActive'],
                'stock' => $this->responseParser->getStock($product, $mainVariation),
                'number' => $mainVariation['number'],
                'manufacturerIdentifier' => $this->responseParser->getManufacturerIdentifier($product),
                'categoryIdentifiers' => $this->responseParser->getCategories($mainVariation, $webstores),
                'defaultCategoryIdentifiers' => $this->responseParser->getDafaultCategories($mainVariation, $webstores),
                'shippingProfileIdentifiers' => $this->responseParser->getShippingProfiles($product),
                'vatRateIdentifier' => $this->responseParser->getVatRateIdentifier($mainVariation),
                'description' => $product['texts'][0]['shortDescription'],
                'longDescription' => $product['texts'][0]['description'],
                'technicalDescription' => $product['texts'][0]['technicalData'],
                'metaTitle' => $product['texts'][0]['name1'],
                'metaDescription' => $product['texts'][0]['metaDescription'],
                'metaKeywords' => $product['texts'][0]['keywords'],
                'metaRobots' => 'INDEX, FOLLOW',
                'translations' => $this->responseParser->getProductTranslations($product['texts']),
                'attributes' => $this->responseParser->getAttributes($product),
            ]);

            $result[] = $object;
        }

        return $result;
    }
}
