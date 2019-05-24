<?php

namespace PlentyConnector\Components\Bundle\PlentymarketsAdapter\ResponseParser;

use DateTimeImmutable;
use PlentyConnector\Components\Bundle\TransferObject\Bundle;
use PlentyConnector\Components\Bundle\TransferObject\BundleProduct\BundleProduct;
use PlentymarketsAdapter\Client\ClientInterface;
use PlentymarketsAdapter\Helper\VariationHelperInterface;
use PlentymarketsAdapter\PlentymarketsAdapter;
use PlentymarketsAdapter\ResponseParser\Product\Price\PriceResponseParserInterface;
use Psr\Log\LoggerInterface;
use SystemConnector\IdentityService\Exception\NotFoundException;
use SystemConnector\IdentityService\IdentityServiceInterface;
use SystemConnector\TransferObject\Language\Language;
use SystemConnector\TransferObject\Product\Product;
use SystemConnector\TransferObject\VatRate\VatRate;
use SystemConnector\ValueObject\Translation\Translation;

class BundleResponseParser implements BundleResponseParserInterface
{
    /**
     * @var IdentityServiceInterface
     */
    private $identityService;

    /**
     * @var PriceResponseParserInterface
     */
    private $priceResponseParser;

    /**
     * @var ClientInterface
     */
    private $client;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var VariationHelperInterface
     */
    private $variationHelper;

    public function __construct(
        IdentityServiceInterface $identityService,
        PriceResponseParserInterface $priceResponseParser,
        VariationHelperInterface $variationHelper,
        ClientInterface $client,
        LoggerInterface $logger
    ) {
        $this->identityService = $identityService;
        $this->priceResponseParser = $priceResponseParser;
        $this->client = $client;
        $this->logger = $logger;
        $this->variationHelper = $variationHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function parse(array $product)
    {
        $bundleVariations = array_filter($product['variations'], static function (array $variation) {
            return $variation['bundleType'] === 'bundle';
        });

        if (empty($bundleVariations)) {
            return [];
        }

        $bundles = [];

        foreach ($bundleVariations as $bundle) {
            if (empty($this->variationHelper->getShopIdentifiers($bundle))) {
                continue;
            }

            $bundles[] = $this->parseBundle($bundle, $product);
        }

        return array_filter($bundles);
    }

    /**
     * @param array $variation
     *
     * @return string
     */
    private function getVatRateIdentifier(array $variation): string
    {
        $vatRateIdentity = $this->identityService->findOneBy([
            'adapterIdentifier' => $variation['vatId'],
            'adapterName' => PlentymarketsAdapter::NAME,
            'objectType' => VatRate::TYPE,
        ]);

        if (null === $vatRateIdentity) {
            throw new NotFoundException('missing mapping for vat rate');
        }

        return $vatRateIdentity->getObjectIdentifier();
    }

    /**
     * @param array $variation
     * @param array $product
     *
     * @return null|Bundle
     */
    private function parseBundle(array $variation, array $product)
    {
        $identity = $this->identityService->findOneOrCreate(
            (string) $variation['id'],
            PlentymarketsAdapter::NAME,
            Bundle::TYPE
        );

        $productIdentity = $this->identityService->findOneBy([
            'adapterIdentifier' => (string) $product['id'],
            'adapterName' => PlentymarketsAdapter::NAME,
            'objectType' => Product::TYPE,
        ]);

        if (null === $productIdentity) {
            $this->logger->notice('product not found', ['bundle' => $variation]);

            return null;
        }

        $bundle = new Bundle();
        $bundle->setIdentifier($identity->getObjectIdentifier());
        $bundle->setName($product['texts'][0]['name1']);
        $bundle->setNumber($variation['number']);
        $bundle->setPosition($variation['position']);
        $bundle->setStockLimitation((bool) $variation['stockLimitation']);
        $bundle->setPrices($this->priceResponseParser->parse($variation));
        $bundle->setVatRateIdentifier($this->getVatRateIdentifier($variation));
        $bundle->setProductIdentifier($productIdentity->getObjectIdentifier());
        $bundle->setAvailableFrom($this->getAvailableFrom($variation));
        $bundle->setAvailableTo($this->getAvailableTo($variation));
        $bundle->setBundleProducts($this->getBundleProducts($variation));
        $bundle->setTranslations($this->getTranslations($product));

        return $bundle;
    }

    /**
     * @param array $mainVariation
     *
     * @return null|DateTimeImmutable
     */
    private function getAvailableFrom(array $mainVariation)
    {
        if (!empty($mainVariation['availableUntil'])) {
            return new DateTimeImmutable('now');
        }

        return null;
    }

    /**
     * @param array $mainVariation
     *
     * @return null|DateTimeImmutable
     */
    private function getAvailableTo(array $mainVariation)
    {
        if (!empty($mainVariation['availableUntil'])) {
            return new DateTimeImmutable($mainVariation['availableUntil']);
        }

        return null;
    }

    /**
     * @param array $product
     *
     * @return Translation[]
     */
    private function getTranslations(array $product): array
    {
        $translations = [];

        foreach ($product['texts'] as $text) {
            $languageIdentifier = $this->identityService->findOneBy([
                'adapterIdentifier' => $text['lang'],
                'adapterName' => PlentymarketsAdapter::NAME,
                'objectType' => Language::TYPE,
            ]);

            if (null === $languageIdentifier) {
                continue;
            }

            $translations[] = Translation::fromArray([
                'languageIdentifier' => $languageIdentifier->getObjectIdentifier(),
                'property' => 'name',
                'value' => $text['name1'],
            ]);
        }

        return $translations;
    }

    /**
     * @param array $elements
     */
    private function addProductNumberToResponse(array &$elements)
    {
        $ids = implode(',', array_column($elements, 'componentVariationId'));
        $variations = $this->client->request('GET', 'items/variations', ['id' => $ids]);

        foreach ($elements as &$element) {
            $matchedVariations = array_filter($variations, static function (array $variation) use ($element) {
                return (int) $element['componentVariationId'] === (int) $variation['id'];
            });

            if (empty($matchedVariations)) {
                continue;
            }

            $variation = array_shift($matchedVariations);

            $element['number'] = $variation['number'];
        }
    }

    /**
     * @param array $variation
     *
     * @return BundleProduct[]
     */
    private function getBundleProducts(array $variation): array
    {
        $url = 'items/' . $variation['itemId'] . '/variations/' . $variation['id'] . '/variation_bundles';
        $elements = $this->client->request('GET', $url);

        $this->addProductNumberToResponse($elements);

        $result = [];
        foreach ($elements as $element) {
            $bundleProduct = new BundleProduct();
            $bundleProduct->setAmount((float) $element['componentQuantity']);
            $bundleProduct->setNumber($element['number']);

            $result[] = $bundleProduct;
        }

        return $result;
    }
}
