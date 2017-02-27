<?php

namespace PlentymarketsAdapter\ResponseParser\Product;

use DateTimeImmutable;
use DateTimeZone;
use PlentyConnector\Connector\IdentityService\IdentityServiceInterface;
use PlentyConnector\Connector\TransferObject\Category\Category;
use PlentyConnector\Connector\TransferObject\CustomerGroup\CustomerGroup;
use PlentyConnector\Connector\TransferObject\Language\Language;
use PlentyConnector\Connector\TransferObject\Manufacturer\Manufacturer;
use PlentyConnector\Connector\TransferObject\Product\LinkedProduct\LinkedProduct;
use PlentyConnector\Connector\TransferObject\Product\Price\Price;
use PlentyConnector\Connector\TransferObject\Product\Product;
use PlentyConnector\Connector\TransferObject\Product\Property\Property;
use PlentyConnector\Connector\TransferObject\Product\Property\Value\Value;
use PlentyConnector\Connector\TransferObject\Product\Variation\Variation;
use PlentyConnector\Connector\TransferObject\ShippingProfile\ShippingProfile;
use PlentyConnector\Connector\TransferObject\Shop\Shop;
use PlentyConnector\Connector\TransferObject\Unit\Unit;
use PlentyConnector\Connector\TransferObject\VatRate\VatRate;
use PlentyConnector\Connector\ValueObject\Attribute\Attribute;
use PlentyConnector\Connector\ValueObject\Translation\Translation;
use PlentymarketsAdapter\Client\ClientInterface;
use PlentymarketsAdapter\Helper\LanguageHelper;
use PlentymarketsAdapter\Helper\MediaCategoryHelper;
use PlentymarketsAdapter\PlentymarketsAdapter;
use PlentymarketsAdapter\ResponseParser\Media\MediaResponseParserInterface;
use Psr\Log\LoggerInterface;

/**
 * Class ProductResponseParser.
 */
class ProductResponseParser implements ProductResponseParserInterface
{
    /**
     * @var IdentityServiceInterface
     */
    private $identityService;

    /**
     * @var ClientInterface
     */
    private $client;

    /**
     * @var LanguageHelper
     */
    private $languageHelper;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * ProductResponseParser constructor.
     *
     * @param IdentityServiceInterface $identityService
     * @param ClientInterface $client
     * @param LanguageHelper $languageHelper
     * @param LoggerInterface $logger
     */
    public function __construct(
        IdentityServiceInterface $identityService,
        ClientInterface $client,
        LanguageHelper $languageHelper,
        LoggerInterface $logger
    ) {
        $this->identityService = $identityService;
        $this->client = $client;
        $this->languageHelper = $languageHelper;
        $this->logger = $logger;
    }

    /**
     * @param array $variations
     *
     * @return array
     */
    public function getMainVariation(array $variations)
    {
        $mainVariation = array_filter($variations, function ($varation) {
            return $varation['isMain'] === true;
        });

        if (empty($mainVariation)) {
            // TODO: throw
        }

        return array_shift($mainVariation);
    }

    /**
     * Returns the matching price configurations. We need a direct mapping in these configurations to find the
     * correct price.
     *
     * @return array
     */
    private function getPriceConfigurations()
    {
        static $priceConfigurations;

        if (null === $priceConfigurations) {
            $priceConfigurations = $this->client->request('GET', 'items/sales_prices');

            $shopIdentities = $this->identityService->findby([
                'adapterName' => PlentymarketsAdapter::NAME,
                'objectType' => Shop::TYPE,
            ]);

            if (empty($shopIdentities)) {
                return $priceConfigurations;
            }

            $priceConfigurations = array_filter($priceConfigurations, function ($priceConfiguration) use ($shopIdentities) {
                foreach ($shopIdentities as $identity) {
                    foreach ($priceConfiguration['clients'] as $client) {
                        if ($client['plentyId'] === -1 || $identity->getAdapterIdentifier() === (string) $client['plentyId']) {
                            return true;
                        }
                    }
                }

                return false;
            });

            if (empty($priceConfigurations)) {
                $this->logger->notice('no valid price configuration found');
            }
        }

        return $priceConfigurations;
    }

    /**
     * @param array $variation
     *
     * @return array
     */
    private function getPrices(array $variation)
    {
        static $customerGroups;

        if (null === $customerGroups) {
            $customerGroups = array_keys($this->client->request('GET', 'accounts/contacts/classes'));
        }

        $priceConfigurations = $this->getPriceConfigurations();

        $temporaryPrices = [];
        foreach ($variation['variationSalesPrices'] as $price) {
            $priceConfiguration = array_filter($priceConfigurations, function ($configuration) use ($price) {
                return $configuration['id'] === $price['salesPriceId'];
            });

            if (empty($priceConfiguration)) {
                // no price configuration found, skip price

                continue;
            }

            $priceConfiguration = array_shift($priceConfiguration);

            $customerClasses = $priceConfiguration['customerClasses'];

            if (count($customerClasses) !== 1 && $customerClasses[0]['customerClassId'] !== -1) {
                foreach ($customerGroups as $group) {
                    $customerGroupIdentity = $this->identityService->findOneBy([
                        'adapterIdentifier' => $group,
                        'adapterName' => PlentymarketsAdapter::NAME,
                        'objectType' => CustomerGroup::TYPE,
                    ]);

                    if (null === $customerGroupIdentity) {
                        // TODO: throw

                        continue;
                    }

                    if (!isset($temporaryPrices[$customerGroupIdentity->getObjectIdentifier()][$priceConfiguration['type']])) {
                        $temporaryPrices[$customerGroupIdentity->getObjectIdentifier()][$priceConfiguration['type']] = [
                            'from' => $priceConfiguration['minimumOrderQuantity'],
                            'price' => $price['price'],
                        ];
                    }
                }
            } else {
                if (!isset($temporaryPrices['default'][$priceConfiguration['type']])) {
                    $temporaryPrices['default'][$priceConfiguration['type']] = [
                        'from' => $priceConfiguration['minimumOrderQuantity'],
                        'price' => $price['price']
                    ];
                }
            }
        }

        /**
         * @var Price[] $prices
         */
        $prices = [];
        foreach ($temporaryPrices as $customerGroup => $priceArray) {
            if ($customerGroup === 'default') {
                $customerGroup = null;
            }

            $price = 0.0;
            $pseudoPrice = 0.0;

            if (isset($priceArray['default'])) {
                $price = (double) $priceArray['default']['price'];
            }

            if (isset($priceArray['default'])) {
                $pseudoPrice = (double) $priceArray['rrp']['price'];
            }

            $prices[] = Price::fromArray([
                'price' => $price,
                'pseudoPrice' => $pseudoPrice,
                'customerGroupIdentifier' => $customerGroup,
                'from' => (int) $priceArray['default']['from'],
                'to' => null,
            ]);
        }

        foreach ($prices as $price) {
            /**
             * @var Price[] $possibleScalePrices
             */
            $possibleScalePrices = array_filter($prices, function(Price $possiblePrice) use ($price) {
                return $possiblePrice->getCustomerGroupIdentifier() === $price->getCustomerGroupIdentifier() &&
                    spl_object_hash($price) !== spl_object_hash($possiblePrice);
            });

            if (empty($possibleScalePrices)) {
                continue;
            }

            usort($possibleScalePrices, function(Price $possibleScalePriceLeft, Price $possibleScalePriceright) {
                if ($possibleScalePriceLeft->getFromAmount() === $possibleScalePriceright->getFromAmount()) {
                    return 0;
                }

                if ($possibleScalePriceLeft->getFromAmount() > $possibleScalePriceright->getFromAmount()) {
                    return 1;
                }

                return -1;
            });

            foreach ($possibleScalePrices as $possibleScalePrice) {
                if ($possibleScalePrice->getFromAmount() > $price->getFromAmount()) {
                    $price->setToAmount($possibleScalePrice->getFromAmount() - 1);

                    break;
                }
            }
        }

        return $prices;
    }

    /**
     * @param array $texts
     * @param array $variation
     * @param array $result
     *
     * @return array
     */
    private function getVariationImages(array $texts, array $variation, array &$result)
    {
        $url = 'items/' . $variation['itemId'] . '/variations/' . $variation['id'] . '/images';
        $images = $this->client->request('GET', $url);

        $imageIdentifiers = array_map(function ($image) use ($texts, &$result) {
            /**
             * @var MediaResponseParserInterface $mediaResponseParser
             */
            $mediaResponseParser = Shopware()->Container()->get('plentmarkets_adapter.response_parser.media');

            if (!empty($image['names'][0]['name'])) {
                $name = $image['names'][0]['name'];
            } else {
                $name = $texts[0]['name1'];
            }

            $media = $mediaResponseParser->parse([
                'mediaCategory' => MediaCategoryHelper::PRODUCT,
                'link' => $image['url'],
                'name' => $name,
                'translations' => $this->getMediaTranslations($image, $texts),
            ]);

            $result[] = $media;

            return $media->getIdentifier();
        }, $images);

        return array_filter($imageIdentifiers);
    }

    /**
     * @param array $variation
     *
     * @return string
     */
    private function getUnitIdentifier(array $variation)
    {
        // Unit
        $unitIdentity = $this->identityService->findOneBy([
            'adapterIdentifier' => $variation['unit']['unitId'],
            'adapterName' => PlentymarketsAdapter::NAME,
            'objectType' => Unit::TYPE,
        ]);

        if (null === $unitIdentity) {
            // TODO: throw
        }

        return $unitIdentity->getObjectIdentifier();
    }

    /**
     * @param array $variation
     *
     * @return string
     */
    public function getVatRateIdentifier(array $variation)
    {
        $vatRateIdentity = $this->identityService->findOneBy([
            'adapterIdentifier' => $variation['vatId'],
            'adapterName' => PlentymarketsAdapter::NAME,
            'objectType' => VatRate::TYPE,
        ]);

        if (null === $vatRateIdentity) {
            // TODO: throw
        }

        return $vatRateIdentity->getObjectIdentifier();
    }

    /**
     * @param array $variation
     *
     * @return null|DateTimeImmutable
     */
    private function getReleaseDate(array $variation)
    {
        if (null !== $variation['releasedAt']) {
            $timezone = new DateTimeZone('UTC');

            return new DateTimeImmutable($variation['releasedAt'], $timezone);
        }

        return null;
    }

    /**
     * @param array $product
     *
     * @return string
     */
    public function getManufacturerIdentifier(array $product)
    {
        $manufacturerIdentity = $this->identityService->findOneOrCreate(
            (string) $product['manufacturerId'],
            PlentymarketsAdapter::NAME,
            Manufacturer::TYPE
        );

        if (null === $manufacturerIdentity) {
            // TODO: throw
        }

        return $manufacturerIdentity->getObjectIdentifier();
    }

    /**
     * @param array $product
     *
     * @return array
     */
    public function getShippingProfiles(array $product)
    {
        $productShippingProfiles = $this->client->request('GET', 'items/' . $product['id'] . '/item_shipping_profiles', [
            'with' => 'names',
            'lang' => implode(',', array_column($this->languageHelper->getLanguages(), 'id')),
        ]);

        $shippingProfiles = [];
        foreach ($productShippingProfiles as $profile) {
            $profileIdentity = $this->identityService->findOneBy([
                'adapterIdentifier' => (string) $profile['profileId'],
                'objectType' => ShippingProfile::TYPE,
                'adapterName' => PlentymarketsAdapter::NAME,
            ]);

            if (null === $profileIdentity) {
                // TODO: notice

                continue;
            }

            $shippingProfiles[] = $profileIdentity->getObjectIdentifier();
        }

        return $shippingProfiles;
    }

    /**
     * @param array $product
     * @param array $texts
     * @param array $result
     *
     * @return array
     */
    public function getImageIdentifiers(array $product, array $texts, array &$result)
    {
        $url = 'items/' . $product['id'] . '/images';
        $images = $this->client->request('GET', $url);

        $imageIdentifiers = array_map(function ($image) use ($texts, &$result) {
            /**
             * @var MediaResponseParserInterface $mediaResponseParser
             */
            $mediaResponseParser = Shopware()->Container()->get('plentmarkets_adapter.response_parser.media');

            if (!empty($image['names'][0]['name'])) {
                $name = $image['names'][0]['name'];
            } else {
                $name = $texts[0]['name1'];
            }

            $media = $mediaResponseParser->parse([
                'mediaCategory' => MediaCategoryHelper::PRODUCT,
                'link' => $image['url'],
                'name' => $name,
                'translations' => $this->getMediaTranslations($image, $texts),
            ]);

            $result[] = $media;

            return $media->getIdentifier();
        }, $images);

        return array_filter($imageIdentifiers);
    }

    /**
     * @param array $mainVariation
     * @param array $webstores
     *
     * @return array
     */
    public function getDafaultCategories(array $mainVariation, array $webstores)
    {
        $defaultCategories = [];

        foreach ($mainVariation['variationDefaultCategory'] as $category) {
            foreach ($mainVariation['variationClients'] as $client) {
                $store = array_filter($webstores, function ($store) use ($client) {
                    return $store['storeIdentifier'] === $client['plentyId'];
                });

                if (empty($store)) {
                    // TODO: notice
                }

                $store = array_shift($store);

                $categoryIdentity = $this->identityService->findOneBy([
                    'adapterIdentifier' => (string) ($store['storeIdentifier'] . '-' . $category['branchId']),
                    'adapterName' => PlentymarketsAdapter::NAME,
                    'objectType' => Category::TYPE,
                ]);

                if (null === $categoryIdentity) {
                    // TODO: notice

                    continue;
                }

                $defaultCategories[] = $categoryIdentity->getObjectIdentifier();
            }
        }

        return $defaultCategories;
    }

    /**
     * @param array $texts
     *
     * @return Translation[]
     */
    public function getProductTranslations(array $texts)
    {
        $translations = [];

        foreach ($texts as $text) {
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

            $translations[] = Translation::fromArray([
                'languageIdentifier' => $languageIdentifier->getObjectIdentifier(),
                'property' => 'description',
                'value' => $text['shortDescription'],
            ]);

            $translations[] = Translation::fromArray([
                'languageIdentifier' => $languageIdentifier->getObjectIdentifier(),
                'property' => 'longDescription',
                'value' => $text['description'],
            ]);

            $translations[] = Translation::fromArray([
                'languageIdentifier' => $languageIdentifier->getObjectIdentifier(),
                'property' => 'technicalDescription',
                'value' => $text['technicalData'],
            ]);

            $translations[] = Translation::fromArray([
                'languageIdentifier' => $languageIdentifier->getObjectIdentifier(),
                'property' => 'metaTitle',
                'value' => $text['name1'],
            ]);

            $translations[] = Translation::fromArray([
                'languageIdentifier' => $languageIdentifier->getObjectIdentifier(),
                'property' => 'metaDescription',
                'value' => $text['metaDescription'],
            ]);

            $translations[] = Translation::fromArray([
                'languageIdentifier' => $languageIdentifier->getObjectIdentifier(),
                'property' => 'metaKeywords',
                'value' => $text['keywords'],
            ]);
        }

        return $translations;
    }

    /**
     * @param $variation
     *
     * @return int
     */
    public function getStock($variation)
    {
        $url = 'items/' . $variation['itemId'] . '/variations/' . $variation['id'] . '/stock';
        $stocks = $this->client->request('GET', $url);

        $summedStocks = 0;

        foreach ($stocks as $stock) {
            if (array_key_exists('netStock', $stock)) {
                $summedStocks += $stock['netStock'];
            }
        }

        return $summedStocks;
    }

    /**
     * @param array $mainVariation
     * @param array $webstores
     *
     * @return array
     */
    public function getCategories(array $mainVariation, array $webstores)
    {
        $categories = [];
        foreach ($mainVariation['variationCategories'] as $category) {
            foreach ($mainVariation['variationClients'] as $client) {
                $store = array_filter($webstores, function ($store) use ($client) {
                    return $store['storeIdentifier'] === $client['plentyId'];
                });

                if (empty($store)) {
                    // TODO: notice

                    continue;
                }

                $store = array_shift($store);

                $categoryIdentity = $this->identityService->findOneBy([
                    'adapterIdentifier' => (string) ($store['storeIdentifier'] . '-' . $category['categoryId']),
                    'adapterName' => PlentymarketsAdapter::NAME,
                    'objectType' => Category::TYPE,
                ]);

                if (null === $categoryIdentity) {
                    // TODO: notice

                    continue;
                }

                $categories[] = $categoryIdentity->getObjectIdentifier();
            }
        }

        return $categories;
    }

    /**
     * @param array $product
     *
     * @return Attribute[]
     */
    public function getAttributes(array $product)
    {
        $attributes = [];

        for ($i = 0; $i < 20; ++$i) {
            $key = 'free' . ($i + 1);

            $attributes[] = Attribute::fromArray([
                'key' => $key,
                'value' => (string) $product[$key],
                'translations' => [],
            ]);
        }

        return $attributes;
    }

    /**
     * @param array $image
     * @param array $productTexts
     *
     * @return array
     */
    private function getMediaTranslations(array $image, array $productTexts)
    {
        $translations = [];

        foreach ($image['names'] as $text) {
            $languageIdentifier = $this->identityService->findOneBy([
                'adapterIdentifier' => $text['lang'],
                'adapterName' => PlentymarketsAdapter::NAME,
                'objectType' => Language::TYPE,
            ]);

            if (null === $languageIdentifier) {
                continue;
            }

            if (!empty($text['name'])) {
                $name = $text['name'];
            } else {
                $name = '';

                foreach ($productTexts as $productText) {
                    if ($text['lang'] === $productText['lang']) {
                        $name = $productText['name1'];
                    }
                }
            }

            $translations[] = Translation::fromArray([
                'languageIdentifier' => $languageIdentifier->getObjectIdentifier(),
                'property' => 'name',
                'value' => $name,
            ]);

            if (!empty($text['alternate'])) {
                $alternate = $text['alternate'];
            } else {
                $alternate = '';

                foreach ($productTexts as $productText) {
                    if ($text['lang'] === $productText['lang']) {
                        $alternate = $productText['name1'];
                    }
                }
            }

            $translations[] = Translation::fromArray([
                'languageIdentifier' => $languageIdentifier->getObjectIdentifier(),
                'property' => 'alternateName',
                'value' => $alternate,
            ]);
        }

        return $translations;
    }

    /**
     * @param array $texts
     * @param array $variations
     * @param array $result
     *
     * @return Variation[]
     */
    public function getVariations(array $texts, $variations, array &$result)
    {
        $mappedVariations = [];

        if (count($variations) > 1) {
            $variations = array_filter($variations, function (array $variation) {
                return !$variation['isMain'];
            });
        }

        $first = true;

        foreach ($variations as $variation) {
            $mappedVariations[] = Variation::fromArray([
                'active' => true,
                'isMain' => $first,
                'stock' => $this->getStock($variation),
                'number' => (string) $variation['number'],
                'ean' => '',
                'model' => $variation['model'],
                'imageIdentifiers' => $this->getVariationImages($texts, $variation, $result),
                'prices' => $this->getPrices($variation),
                'purchasePrice' => (double) $variation['purchasePrice'],
                'unitIdentifier' => $this->getUnitIdentifier($variation),
                'content' => (double) $variation['unit']['content'],
                'maximumOrderQuantity' => (int) $variation['maximumOrderQuantity'],
                'minimumOrderQuantity' => (int) $variation['minimumOrderQuantity'],
                'intervalOrderQuantity' => (int) $variation['intervalOrderQuantity'],
                'releaseDate' => $this->getReleaseDate($variation),
                'shippingTime' => $this->getShippingTime($variation),
                'width' => (int) $variation['widthMM'],
                'height' => (int) $variation['heightMM'],
                'length' => (int) $variation['lengthMM'],
                'weight' => (int) $variation['weightNetG'],
                'attributes' => [],
                'properties' => $this->getVariationProperties($variation),
            ]);

            $first = false;
        }

        return $mappedVariations;
    }

    /**
     * @param $product
     *
     * @return LinkedProduct[]
     */
    public function getLinkedProducts(array $product)
    {
        $linkedProducts = $images = $this->client->request('GET', 'items/' . $product['id'] . '/item_cross_selling');

        $result = [];
        foreach ($linkedProducts as $linkedProduct) {
            if ($linkedProduct['relationship'] === 'Similar') {
                $type = LinkedProduct::TYPE_SIMILAR;
            } elseif ($linkedProduct['relationship'] === 'Accessory') {
                $type = LinkedProduct::TYPE_ACCESSORY;
            } elseif ($linkedProduct['relationship'] === 'ReplacementPart') {
                $type = LinkedProduct::TYPE_REPLACEMENT;
            } else {
                continue;
            }

            $productIdentity = $this->identityService->findOneBy([
                'adapterIdentifier' => $linkedProduct['crossItemId'],
                'adapterName' => PlentymarketsAdapter::NAME,
                'objectType' => Product::TYPE,
            ]);

            if (null === $productIdentity) {
                // TODO: throw event to trigger import of missing product

                continue;
            }

            $result[] = LinkedProduct::fromArray([
                'type' => $type,
                'productIdentifier' => $productIdentity->getObjectIdentifier(),
            ]);
        }

        return $result;
    }

    /**
     * TODO
     *
     * @param array $product
     *
     * @return array
     */
    public function getDocuments(array $product)
    {
        return [];
    }

    /**
     * TODO
     *
     * @param $product
     *
     * @return Property[]
     */
    public function getProperties(array $product)
    {

        /* TODO:
            post: rest/items/properties/{id}/selections
            get: rest/items/properties/{id}/selections (Gibt alle Werte für die Property ID aus)
            get: rest/items/properties/{id}/selections/{lang} (Gibt einen Wert für die Property ID in der entspr. Sprache aus)
            put: rest/items/properties/{id}/selections/{lang}
            delete: rest/items/properties/{id}/selections/{lang}
        */

        return [];
    }

    /**
     * @param array $variation
     *
     * @return integer
     */
    private function getShippingTime(array $variation)
    {
        static $shippingConfigurations;

        if (null === $shippingConfigurations) {
            try {
                $shippingConfigurations = $this->client->request('GET', 'availabilities');
            } catch (\Exception $exception) {
                // not implemented on all systems yet

                $shippingConfigurations = [];
            }
        }

        $shippingConfiguration = array_filter($shippingConfigurations, function(array $configuration) use ($variation) {
            return $configuration['id'] ===  $variation['availability'];
        });

        if (!empty($shippingConfiguration)) {
            $shippingConfiguration = array_shift($shippingConfiguration);

            return $shippingConfiguration['averageDays'];
        }

        return 0;
    }

    /**
     * @param $variation
     *
     * @return Property[]
     */
    public function getVariationProperties(array $variation)
    {
        static $attributes;

        $result = [];
        foreach ($variation['variationAttributeValues'] as $attributeValue) {
            if (!isset($attributes[$attributeValue['attributeId']])) {
                $attributes[$attributeValue['attributeId']] = $this->client->request('GET', 'items/attributes/' . $attributeValue['attributeId']);
                $attributes[$attributeValue['attributeId']]['names'] = $this->client->request('GET', 'items/attributes/' . $attributeValue['attributeId'] . '/names');
                $attributes[$attributeValue['attributeId']]['values'] = [];

                $values = $this->client->request('GET', 'items/attributes/' . $attributeValue['attributeId'] . '/values');
                foreach ($values as $value) {
                    $attributes[$attributeValue['attributeId']]['values'][$value['id']] = $value;
                    $attributes[$attributeValue['attributeId']]['values'][$value['id']]['names'] = $this->client->request('GET', 'items/attribute_values/' . $value['id'] . '/names');
                }
            }

            $propertyNames = $attributes[$attributeValue['attributeId']]['names'];
            $valueNames = $attributes[$attributeValue['attributeId']]['values'][$attributeValue['valueId']]['names'];

            $value = Value::fromArray([
                'value' => $valueNames[0]['name'],
                'translations' => $this->getPropertyValueTranslations($valueNames),
            ]);

            $result[] = Property::fromArray([
                'name' => $propertyNames[0]['name'],
                'values' => [$value],
                'translations' => $this->getPropertyTranslations($propertyNames),
            ]);
        }

        return $result;
    }

    /**
     * @param array $names
     *
     * @return Translation[]
     */
    private function getPropertyValueTranslations(array $names)
    {
        $translations = [];

        foreach ($names as $name) {
            $languageIdentifier = $this->identityService->findOneBy([
                'adapterIdentifier' => $name['lang'],
                'adapterName' => PlentymarketsAdapter::NAME,
                'objectType' => Language::TYPE,
            ]);

            if (null === $languageIdentifier) {
                continue;
            }

            $translations[] = Translation::fromArray([
                'languageIdentifier' => $languageIdentifier->getObjectIdentifier(),
                'property' => 'value',
                'value' => $name['name'],
            ]);
        }

        return $translations;
    }

    /**
     * @param array $names
     *
     * @return Translation[]
     */
    private function getPropertyTranslations(array $names)
    {
        $translations = [];

        foreach ($names as $name) {
            $languageIdentifier = $this->identityService->findOneBy([
                'adapterIdentifier' => $name['lang'],
                'adapterName' => PlentymarketsAdapter::NAME,
                'objectType' => Language::TYPE,
            ]);

            if (null === $languageIdentifier) {
                continue;
            }

            $translations[] = Translation::fromArray([
                'languageIdentifier' => $languageIdentifier->getObjectIdentifier(),
                'property' => 'name',
                'value' => $name['name'],
            ]);
        }

        return $translations;
    }

    /**
     * @param array $mainVariation
     *
     * @return array
     */
    public function getShopIdentifiers(array $mainVariation)
    {
        $identifiers = [];

        foreach ($mainVariation['variationClients'] as $client) {
            $identity = $this->identityService->findOneBy([
                'adapterIdentifier' => $client['plentyId'],
                'adapterName' => PlentymarketsAdapter::NAME,
                'objectType' => Shop::TYPE,
            ]);

            if (null === $identity) {
                $this->logger->notice('shop not found', $client);

                continue;
            }

            $identifiers[] = $identity->getObjectIdentifier();
        }

        return $identifiers;
    }
}
