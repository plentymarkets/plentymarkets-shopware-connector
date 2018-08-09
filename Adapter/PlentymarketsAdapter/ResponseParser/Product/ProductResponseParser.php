<?php

namespace PlentymarketsAdapter\ResponseParser\Product;

use DateTimeImmutable;
use PlentyConnector\Connector\ConfigService\ConfigServiceInterface;
use PlentyConnector\Connector\IdentityService\Exception\NotFoundException;
use PlentyConnector\Connector\IdentityService\IdentityServiceInterface;
use PlentyConnector\Connector\TransferObject\Category\Category;
use PlentyConnector\Connector\TransferObject\Language\Language;
use PlentyConnector\Connector\TransferObject\Manufacturer\Manufacturer;
use PlentyConnector\Connector\TransferObject\Product\Image\Image;
use PlentyConnector\Connector\TransferObject\Product\LinkedProduct\LinkedProduct;
use PlentyConnector\Connector\TransferObject\Product\Product;
use PlentyConnector\Connector\TransferObject\Product\Property\Property;
use PlentyConnector\Connector\TransferObject\Product\Property\Value\Value;
use PlentyConnector\Connector\TransferObject\Product\Variation\Variation;
use PlentyConnector\Connector\TransferObject\ShippingProfile\ShippingProfile;
use PlentyConnector\Connector\TransferObject\TransferObjectInterface;
use PlentyConnector\Connector\TransferObject\VatRate\VatRate;
use PlentyConnector\Connector\ValueObject\Attribute\Attribute;
use PlentyConnector\Connector\ValueObject\Translation\Translation;
use PlentymarketsAdapter\Client\ClientInterface;
use PlentymarketsAdapter\Helper\VariationHelperInterface;
use PlentymarketsAdapter\PlentymarketsAdapter;
use PlentymarketsAdapter\ReadApi\Item\Property\Name as NameApi;
use PlentymarketsAdapter\ReadApi\Item\Property\Selection as SelectionApi;
use PlentymarketsAdapter\ResponseParser\Product\Image\ImageResponseParserInterface;
use PlentymarketsAdapter\ResponseParser\Product\Variation\VariationResponseParserInterface;
use Psr\Log\LoggerInterface;

/**
 * Class ProductResponseParser.
 */
class ProductResponseParser implements ProductResponseParserInterface
{
    /**
     * @var ConfigServiceInterface
     */
    private $configService;

    /**
     * @var IdentityServiceInterface
     */
    private $identityService;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var ImageResponseParserInterface
     */
    private $imageResponseParser;

    /**
     * @var VariationResponseParserInterface
     */
    private $variationResponseParser;

    /**
     * @var SelectionApi
     */
    private $itemsPropertiesSelectionsApi;

    /**
     * @var NameApi
     */
    private $itemsPropertiesNamesApi;

    /**
     * @var VariationHelperInterface
     */
    private $variationHelper;

    /**
     * ProductResponseParser constructor.
     *
     * @param ConfigServiceInterface           $configService
     * @param IdentityServiceInterface         $identityService
     * @param LoggerInterface                  $logger
     * @param ImageResponseParserInterface     $imageResponseParser
     * @param VariationResponseParserInterface $variationResponseParser
     * @param VariationHelperInterface         $variationHelper
     * @param ClientInterface                  $client
     */
    public function __construct(
        ConfigServiceInterface $configService,
        IdentityServiceInterface $identityService,
        LoggerInterface $logger,
        ImageResponseParserInterface $imageResponseParser,
        VariationResponseParserInterface $variationResponseParser,
        VariationHelperInterface $variationHelper,
        ClientInterface $client
    ) {
        $this->configService = $configService;
        $this->identityService = $identityService;
        $this->logger = $logger;
        $this->imageResponseParser = $imageResponseParser;
        $this->variationResponseParser = $variationResponseParser;
        $this->variationHelper = $variationHelper;

        //TODO: inject when refactoring this class
        $this->itemsPropertiesSelectionsApi = new SelectionApi($client);
        $this->itemsPropertiesNamesApi = new NameApi($client);
    }

    /**
     * @param array $product
     *
     * @return TransferObjectInterface[]
     */
    public function parse(array $product)
    {
        $result = [];

        $mainVariation = $this->getMainVariation($product['variations']);

        if (empty($mainVariation)) {
            return [];
        }

        $shopIdentifiers = $this->variationHelper->getShopIdentifiers($mainVariation);

        if (empty($shopIdentifiers)) {
            return [];
        }

        foreach ($product['variations'] as $val => $key) {
            $variantShopIdentifiers = $this->variationHelper->getShopIdentifiers($key);
            if (empty($variantShopIdentifiers)) {
                unset($product['variations'][$val]);
            }
        }

        $identity = $this->identityService->findOneOrCreate(
            (string) $product['id'],
            PlentymarketsAdapter::NAME,
            Product::TYPE
        );

        $candidatesForProcessing = $this->variationResponseParser->parse($product);

        if (empty($candidatesForProcessing)) {
            return [];
        }

        $variations = array_filter($candidatesForProcessing, function (TransferObjectInterface $object) {
            return $object instanceof Variation;
        });

        $productObject = new Product();
        $productObject->setIdentifier($identity->getObjectIdentifier());
        $productObject->setName((string) $product['texts'][0]['name1']);
        $productObject->setActive($this->getActive($variations, $mainVariation));
        $productObject->setNumber($this->getProductNumber($variations));
        $productObject->setShopIdentifiers($shopIdentifiers);
        $productObject->setManufacturerIdentifier($this->getManufacturerIdentifier($product));
        $productObject->setCategoryIdentifiers($this->getCategories($mainVariation));
        $productObject->setDefaultCategoryIdentifiers($this->getDafaultCategories($mainVariation));
        $productObject->setShippingProfileIdentifiers($this->getShippingProfiles($product));
        $productObject->setImages($this->getImages($product, $product['texts'], $result));
        $productObject->setVatRateIdentifier($this->getVatRateIdentifier($mainVariation));
        $productObject->setStockLimitation($this->getStockLimitation($product));
        $productObject->setDescription((string) $product['texts'][0]['shortDescription']);
        $productObject->setLongDescription((string) $product['texts'][0]['description']);
        $productObject->setTechnicalDescription((string) $product['texts'][0]['technicalData']);
        $productObject->setMetaTitle((string) $product['texts'][0]['name1']);
        $productObject->setMetaDescription((string) $product['texts'][0]['metaDescription']);
        $productObject->setMetaKeywords((string) $product['texts'][0]['keywords']);
        $productObject->setMetaRobots('INDEX, FOLLOW');
        $productObject->setLinkedProducts($this->getLinkedProducts($product));
        $productObject->setProperties($this->getProperties($mainVariation));
        $productObject->setTranslations($this->getProductTranslations($product['texts']));
        $productObject->setAvailableFrom($this->getAvailableFrom($mainVariation));
        $productObject->setAvailableTo($this->getAvailableTo($mainVariation));
        $productObject->setAttributes($this->getAttributes($product));
        $productObject->setVariantConfiguration($this->getVariantConfiguration($variations));

        $result[$productObject->getIdentifier()] = $productObject;

        $candidatesForProcessing = $this->addProductAttributesToVariation($productObject, $candidatesForProcessing);

        return array_merge($result, $candidatesForProcessing);
    }

    /**
     * @param Product                   $product
     * @param TransferObjectInterface[] $candidatesForProcessing
     *
     * @return TransferObjectInterface[]
     */
    private function addProductAttributesToVariation(Product $product, array $candidatesForProcessing = [])
    {
        return array_map(function (TransferObjectInterface $object) use ($product) {
            if (!($object instanceof Variation)) {
                return $object;
            }

            $object->setAttributes(array_merge($object->getAttributes(), $product->getAttributes()));

            return $object;
        }, $candidatesForProcessing);
    }

    /**
     * @param array $product
     *
     * @return bool
     */
    private function getStockLimitation(array $product)
    {
        foreach ($product['variations'] as $variation) {
            if ($variation['stockLimitation']) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param array $variations
     *
     * @return array
     */
    private function getMainVariation(array $variations)
    {
        $mainVariation = array_filter($variations, function ($varation) {
            return $varation['isMain'] === true;
        });

        if (empty($mainVariation)) {
            return [];
        }

        return array_shift($mainVariation);
    }

    /**
     * @param array $variation
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
     * @param array $product
     *
     * @return string
     */
    private function getManufacturerIdentifier(array $product)
    {
        $manufacturerIdentity = $this->identityService->findOneOrCreate(
            (string) $product['manufacturerId'],
            PlentymarketsAdapter::NAME,
            Manufacturer::TYPE
        );

        if (null === $manufacturerIdentity) {
            throw new NotFoundException('missing mapping for manufacturer');
        }

        return $manufacturerIdentity->getObjectIdentifier();
    }

    /**
     * @param array $product
     *
     * @return array
     */
    private function getShippingProfiles(array $product)
    {
        $shippingProfiles = [];
        foreach ($product['shippingProfiles'] as $profile) {
            $profileIdentity = $this->identityService->findOneBy([
                'adapterIdentifier' => (string) $profile['profileId'],
                'adapterName' => PlentymarketsAdapter::NAME,
                'objectType' => ShippingProfile::TYPE,
            ]);

            if (null === $profileIdentity) {
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
     * @return Image[]
     */
    private function getImages(array $product, array $texts, array &$result)
    {
        $images = [];
        foreach ($product['itemImages'] as $entry) {
            $images[] = $this->imageResponseParser->parseImage($entry, $texts, $result);
        }

        return array_filter($images);
    }

    /**
     * @param array $mainVariation
     *
     * @return array
     */
    private function getDafaultCategories(array $mainVariation)
    {
        $defaultCategories = [];

        foreach ($mainVariation['variationDefaultCategory'] as $category) {
            $categoryIdentity = $this->identityService->findOneBy([
                'adapterIdentifier' => (string) $category['branchId'],
                'adapterName' => PlentymarketsAdapter::NAME,
                'objectType' => Category::TYPE,
            ]);

            if (null === $categoryIdentity) {
                $this->logger->notice('missing mapping for category', ['category' => $category]);

                continue;
            }

            $defaultCategories[] = $categoryIdentity->getObjectIdentifier();
        }

        return $defaultCategories;
    }

    /**
     * @param array $texts
     *
     * @return Translation[]
     */
    private function getProductTranslations(array $texts)
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
     * @param array $mainVariation
     *
     * @return array
     */
    private function getCategories(array $mainVariation)
    {
        $categories = [];
        foreach ($mainVariation['variationCategories'] as $category) {
            $categoryIdentity = $this->identityService->findOneBy([
                'adapterIdentifier' => (string) $category['categoryId'],
                'adapterName' => PlentymarketsAdapter::NAME,
                'objectType' => Category::TYPE,
            ]);

            if (null === $categoryIdentity) {
                $this->logger->notice('missing mapping for category', ['category' => $category]);

                continue;
            }

            $categories[] = $categoryIdentity->getObjectIdentifier();
        }

        return $categories;
    }

    /**
     * @param array $product
     *
     * @return LinkedProduct[]
     */
    private function getLinkedProducts(array $product)
    {
        $result = [];

        foreach ($product['itemCrossSelling'] as $linkedProduct) {
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
                $this->logger->notice('linked product not found', ['linkedProduct' => $linkedProduct]);

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
     * @param array $mainVariation
     *
     * @return Property[]
     */
    private function getProperties(array $mainVariation)
    {
        $result = [];

        $properties = $mainVariation['variationProperties'];

        static $propertyNames;

        foreach ($properties as $property) {
            if (!$property['property']['isSearchable']) {
                continue;
            }

            if (!isset($propertyNames[$property['property']['id']])) {
                $propertyName = $this->itemsPropertiesNamesApi->findOne($property['property']['id']);

                $propertyNames[$property['property']['id']] = $propertyName;
            } else {
                $propertyName = $propertyNames[$property['property']['id']];
            }

            $translations = [];
            foreach ($propertyName as $name) {
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

            $values = [];

            if ($property['property']['valueType'] === 'text') {
                if (empty($property['names'][0]['value'])) {
                    continue;
                }

                $valueTranslations = [];
                foreach ($property['names'] as $name) {
                    $languageIdentifier = $this->identityService->findOneBy([
                        'adapterIdentifier' => $name['lang'],
                        'adapterName' => PlentymarketsAdapter::NAME,
                        'objectType' => Language::TYPE,
                    ]);

                    if (null === $languageIdentifier) {
                        continue;
                    }

                    $valueTranslations[] = Translation::fromArray([
                        'languageIdentifier' => $languageIdentifier->getObjectIdentifier(),
                        'property' => 'value',
                        'value' => $name['value'],
                    ]);
                }

                $values[] = Value::fromArray([
                    'value' => (string) $property['names'][0]['value'],
                    'translations' => $valueTranslations,
                ]);
            } elseif ($property['property']['valueType'] === 'int') {
                if (null === $property['valueInt']) {
                    continue;
                }

                $values[] = Value::fromArray([
                    'value' => (string) $property['valueInt'],
                ]);
            } elseif ($property['property']['valueType'] === 'float') {
                if (null === $property['valueFloat']) {
                    continue;
                }

                $values[] = Value::fromArray([
                    'value' => (string) $property['valueFloat'],
                ]);
            } elseif ($property['property']['valueType'] === 'file') {
                $this->logger->notice('file properties are not supported', ['variation', $mainVariation['id']]);

                continue;
            } elseif ($property['property']['valueType'] === 'selection') {
                static $selections;

                if (null === $property['propertySelectionId']) {
                    continue;
                }

                if (!isset($selections[$property['propertyId']])) {
                    $selection = $this->itemsPropertiesSelectionsApi->findOne($property['propertyId']);

                    foreach ($selection as $element) {
                        $selections[$property['propertyId']][$element['id']] = $element;
                        $selections[$property['propertyId']][$element['id']]['translations'] = [];

                        $languageIdentifier = $this->identityService->findOneBy([
                            'adapterIdentifier' => $element['lang'],
                            'adapterName' => PlentymarketsAdapter::NAME,
                            'objectType' => Language::TYPE,
                        ]);

                        if (null !== $languageIdentifier) {
                            $translation = Translation::fromArray([
                                'languageIdentifier' => $languageIdentifier->getObjectIdentifier(),
                                'property' => 'value',
                                'value' => $element['name'],
                            ]);

                            $selections[$property['propertyId']][$element['id']]['translations'] = [$translation];
                        }
                    }
                }

                $selectionValue = (string) $selections[$property['propertyId']][$property['propertySelectionId']]['name'];

                if (empty($selectionValue)) {
                    continue;
                }

                $values[] = Value::fromArray([
                    'value' => $selectionValue,
                    'translations' => $selections[$property['propertyId']][$property['propertySelectionId']]['translations'],
                ]);
            }

            $result[] = Property::fromArray([
                'name' => $propertyName[0]['name'],
                'values' => $values,
                'translations' => $translations,
            ]);
        }

        return $result;
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
     * @param Variation[] $variations
     * @param array       $mainVariation
     *
     * @return bool
     */
    private function getActive(array $variations = [], array $mainVariation)
    {
        $checkInactiveMainVariation = json_decode($this->configService->get('check_active_main_variation'));

        if (!$mainVariation['isActive'] && !$checkInactiveMainVariation) {
            return false;
        }

        foreach ($variations as $variation) {
            if ($variation->getActive()) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param Variation[] $variations
     *
     * @return Property[]
     */
    private function getVariantConfiguration(array $variations = [])
    {
        $properties = [];

        foreach ($variations as $variation) {
            $properties = array_merge($properties, $variation->getProperties());
        }

        return $properties;
    }

    /**
     * @param Variation[] $variations
     *
     * @return string
     */
    private function getProductNumber(array $variations = [])
    {
        $variation = array_shift($variations);

        return $variation->getNumber();
    }

    /**
     * @param array $product
     *
     * @return Attribute[]
     */
    private function getAttributes(array $product)
    {
        $attributes = [];

        for ($i = 0; $i < 20; ++$i) {
            $key = 'free' . ($i + 1);

            if (!array_key_exists($key, $product)) {
                continue;
            }

            $attributes[] = Attribute::fromArray([
                'key' => $key,
                'value' => (string) $product[$key],
            ]);
        }

        $attributes[] = $this->getShortDescriptionAsAttribute($product);

        return $attributes;
    }

    /**
     * @param array $product
     *
     * @return Attribute
     */
    private function getShortDescriptionAsAttribute(array $product)
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
                'property' => 'value',
                'value' => $text['shortDescription'],
            ]);
        }

        $attribute = new Attribute();
        $attribute->setKey('shortDescription');
        $attribute->setValue((string) $product['texts'][0]['shortDescription']);
        $attribute->setTranslations($translations);

        return $attribute;
    }
}
