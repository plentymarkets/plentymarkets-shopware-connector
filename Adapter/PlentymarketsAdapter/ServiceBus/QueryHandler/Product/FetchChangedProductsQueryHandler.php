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
        $webstores = $this->client->request('GET', 'webstores');

        /*
            'updatedBetween',
            'variationUpdatedBetween',
            'variationRelatedUpdatedBetween'
         */

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

            $object = Product::fromArray([
                'identifier' => $identity->getObjectIdentifier(),
                'name' => $product['texts'][0]['name1'],
                'active' => $product['isActive'],
                'stock' => $this->responseParser->getStock($product, $mainVariation),
                'number' => $mainVariation['number'],
                'manufacturerIdentifier' => $this->responseParser->getManufacturerIdentifier($product),
                'categoryIdentifiers' => $this->responseParser->getCategories($mainVariation, $webstores),
                'defaultCategoryIdentifiers' => $this->responseParser->getDafaultCategories($mainVariation, $webstores),
                'imageIdentifiers' => $this->responseParser->getImageIdentifiers($product, $result),
                'prices' => $this->responseParser->getPrices($mainVariation),
                'unitIdentifier' => $this->responseParser->getUnitIdentifier($mainVariation),
                'content' => (float) $mainVariation['unit']['content'],
                'packagingUnit' => '',
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
