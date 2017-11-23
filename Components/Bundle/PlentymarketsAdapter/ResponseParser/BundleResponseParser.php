<?php

namespace PlentyConnector\Components\Bundle\PlentymarketsAdapter\ResponseParser;

use DateTimeImmutable;
use PlentyConnector\Components\Bundle\TransferObject\Bundle;
use PlentyConnector\Components\Bundle\TransferObject\BundleProduct\BundleProduct;
use PlentyConnector\Connector\IdentityService\Exception\NotFoundException;
use PlentyConnector\Connector\IdentityService\IdentityServiceInterface;
use PlentyConnector\Connector\TransferObject\Language\Language;
use PlentyConnector\Connector\TransferObject\Product\Product;
use PlentyConnector\Connector\TransferObject\VatRate\VatRate;
use PlentyConnector\Connector\ValueObject\Translation\Translation;
use PlentymarketsAdapter\Client\ClientInterface;
use PlentymarketsAdapter\PlentymarketsAdapter;
use PlentymarketsAdapter\ResponseParser\Product\Price\PriceResponseParserInterface;
use Psr\Log\LoggerInterface;

/**
 * Class BundleResponseParser
 */
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
     * BundleResponseParser constructor.
     *
     * @param IdentityServiceInterface     $identityService
     * @param PriceResponseParserInterface $priceResponseParser
     * @param ClientInterface              $client
     * @param LoggerInterface              $logger
     */
    public function __construct(
        IdentityServiceInterface $identityService,
        PriceResponseParserInterface $priceResponseParser,
        ClientInterface $client,
        LoggerInterface $logger
    ) {
        $this->identityService = $identityService;
        $this->priceResponseParser = $priceResponseParser;
        $this->client = $client;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function parse(array $product)
    {
        $bundleVariations = array_filter($product['variations'], function (array $variation) {
            return $variation['bundleType'] === 'bundle';
        });

        if (empty($bundleVariations)) {
            return [];
        }

        $bundles = [];

        foreach ($bundleVariations as $bundle) {
            $bundles[] = $this->parseBundle($bundle, $product);
        }

        return array_filter($bundles);
    }

    /**
     * @param array $variation
     *
     * @throws NotFoundException
     *
     * @return string
     */
    private function getVatRateIdentifier(array $variation)
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
     * @return Bundle
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
        $bundle->setPosition($product['position']);
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
    private function getTranslations(array $product)
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
            $matchedVariations = array_filter($variations, function (array $variation) use ($element) {
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
    private function getBundleProducts(array $variation)
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
