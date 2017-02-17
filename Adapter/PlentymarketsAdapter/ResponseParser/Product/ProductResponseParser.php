<?php

namespace PlentymarketsAdapter\ResponseParser\Product;

use PlentyConnector\Connector\IdentityService\IdentityServiceInterface;
use PlentyConnector\Connector\TransferObject\Category\Category;
use PlentyConnector\Connector\TransferObject\Language\Language;
use PlentyConnector\Connector\TransferObject\Manufacturer\Manufacturer;
use PlentyConnector\Connector\TransferObject\Product\Price\Price;
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
            // throw NoMainVariatonException
        }

        return array_shift($mainVariation);
    }

    /**
     * @param $mainVariation
     *
     * @return array
     */
    public function getPrices($mainVariation)
    {
        $customerGroups = array_keys($this->client->request('GET', 'accounts/contacts/classes'));

        $priceConfigurations = $this->getPriceConfigurations();

        $tmporaryPrices = [];
        foreach ($mainVariation['variationSalesPrices'] as $price) {
            $priceConfiguration = array_filter($priceConfigurations, function ($configuration) use ($price) {
                return $configuration['id'] === $price['salesPriceId'];
            });

            if (empty($priceConfiguration)) {
                // no price configuration found, skip price

                continue;
            }

            $priceConfiguration = array_shift($priceConfiguration);

            $customerClasses = $priceConfiguration['customerClasses'];

            if (count($customerClasses) === 1 && $customerClasses[0]['customerClassId'] === -1) {
                foreach ($customerGroups as $group) {
                    $customerGroupIdentity = $this->identityService->findOneBy([
                        'adapterIdentifier' => $group,
                        'adapterName' => PlentymarketsAdapter::NAME,
                        'objectType' => CustomerGroup::TYPE,
                    ]);

                    if (null === $customerGroupIdentity) {
                        // throw
                    }

                    $tmporaryPrices[$customerGroupIdentity->getObjectIdentifier()][$priceConfiguration['type']] = $price['price'];
                }
            }
        }

        // TODO: minimumOrderQuantity Mengenstaffeln
        $prices = [];
        foreach ($tmporaryPrices as $customerGroup => $priceArray) {
            $prices[] = Price::fromArray([
                'price' => (float) $priceArray['default'],
                'pseudoPrice' => (float) $priceArray['rrp'],
                'customerGroupIdentifier' => $customerGroup,
                'from' => 1,
                'to' => null,
                'percent' => 0,
            ]);
        }

        return $prices;
    }

    /**
     * @param array $product
     * @param array $result
     *
     * @return array
     */
    public function getImageIdentifiers(array $product, array &$result)
    {
        $images = $this->client->request('GET', 'items/' . $product['id'] . '/images', [
            'with' => 'names',
            'lang' => implode(',', array_column($this->languageHelper->getLanguages(), 'id')),
        ]);

        $imageIdentifiers = array_map(function ($image) use ($product, &$result) {
            /**
             * @var MediaResponseParserInterface
             */
            $mediaResponseParser = Shopware()->Container()->get('plentmarkets_adapter.response_parser.media');

            if (!empty($image['names'][0]['name'])) {
                $name = $image['names'][0]['name'];
            } else {
                $name = $product['texts'][0]['name1'];
            }

            $media = $mediaResponseParser->parse([
                'mediaCategory' => MediaCategoryHelper::PRODUCT,
                'link' => $image['url'],
                'name' => $name,
                'translations' => $this->getMediaTranslations($image, $product['texts']),
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
    public function getUnitIdentifier(array $variation)
    {
        // Unit
        $unitIdentity = $this->identityService->findOneBy([
            'adapterIdentifier' => $variation['unit']['unitId'],
            'adapterName' => PlentymarketsAdapter::NAME,
            'objectType' => Unit::TYPE,
        ]);

        if (null === $unitIdentity) {
            // throw
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
            // throw
        }

        return $vatRateIdentity->getObjectIdentifier();
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
            // throw
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
        $productShippingProfiles = $this->client->request('GET', 'items/' . $product['id'] . '/item_shipping_profiles',
            [
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
                // throw
            }

            $shippingProfiles[] = $profileIdentity->getObjectIdentifier();
        }

        return $shippingProfiles;
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
                    // throw
                }

                $store = array_shift($store);

                $categoryIdentity = $this->identityService->findOneBy([
                    'adapterIdentifier' => (string) ($store['id'] . '-' . $category['branchId']),
                    'adapterName' => PlentymarketsAdapter::NAME,
                    'objectType' => Category::TYPE,
                ]);

                if (null === $categoryIdentity) {
                    // throw
                }

                $defaultCategories[] = $categoryIdentity->getObjectIdentifier();
            }
        }

        return $defaultCategories;
    }

    /**
     * @param array $texts
     *
     * @return array
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
     * @param $product
     * @param $variation
     *
     * @return int
     */
    public function getStock($product, $variation)
    {
        $url = 'items/' . $product['id'] . '/variations/' . $variation['id'] . '/stock';
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
                    // notice

                    continue;
                }

                $store = array_shift($store);

                $categoryIdentity = $this->identityService->findOneBy([
                    'adapterIdentifier' => (string) ($store['id'] . '-' . $category['categoryId']),
                    'adapterName' => PlentymarketsAdapter::NAME,
                    'objectType' => Category::TYPE,
                ]);

                if (null === $categoryIdentity) {
                    // notice

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
     * Returns the matching price configurations. We need a direct mapping in these configurations to find the
     * correct price.
     *
     * @return array
     */
    private function getPriceConfigurations()
    {
        $priceConfigurations = $this->client->request('GET', 'items/sales_prices');

        $shopIdentities = $this->identityService->findby([
            'adapterName' => PlentymarketsAdapter::NAME,
            'objectType' => Shop::TYPE,
        ]);

        if (empty($shopIdentities)) {
            return [];
        }

        $priceConfigurations = array_filter($priceConfigurations, function ($priceConfiguration) use ($shopIdentities) {
            foreach ($shopIdentities as $identity) {
                foreach ($priceConfiguration['clients'] as $client) {
                    if ($identity->getAdapterIdentifier() === (string) $client['plentyId']) {
                        return true;
                    }
                }
            }

            return false;
        });

        if (empty($priceConfigurations)) {
            $this->logger->notice('no valid price configuration found');
        }

        return $priceConfigurations;
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
}
