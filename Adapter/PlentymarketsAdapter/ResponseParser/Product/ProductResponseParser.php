<?php

namespace PlentymarketsAdapter\ResponseParser\Product;

use DateTimeImmutable;
use PlentymarketsAdapter\Client\ClientInterface;
use PlentymarketsAdapter\Helper\VariationHelperInterface;
use PlentymarketsAdapter\PlentymarketsAdapter;
use PlentymarketsAdapter\ReadApi\Item\Property\Group as PropertyGroupApi;
use PlentymarketsAdapter\ReadApi\Item\Property\Name as PropertyNameApi;
use PlentymarketsAdapter\ResponseParser\Product\Image\ImageResponseParserInterface;
use PlentymarketsAdapter\ResponseParser\Product\Variation\VariationResponseParserInterface;
use Psr\Log\LoggerInterface;
use SystemConnector\ConfigService\ConfigServiceInterface;
use SystemConnector\IdentityService\Exception\NotFoundException;
use SystemConnector\IdentityService\IdentityServiceInterface;
use SystemConnector\TransferObject\Category\Category;
use SystemConnector\TransferObject\Language\Language;
use SystemConnector\TransferObject\Manufacturer\Manufacturer;
use SystemConnector\TransferObject\Product\Badge\Badge;
use SystemConnector\TransferObject\Product\Image\Image;
use SystemConnector\TransferObject\Product\LinkedProduct\LinkedProduct;
use SystemConnector\TransferObject\Product\Product;
use SystemConnector\TransferObject\Product\Property\Property;
use SystemConnector\TransferObject\Product\Property\Value\Value;
use SystemConnector\TransferObject\Product\Variation\Variation;
use SystemConnector\TransferObject\ShippingProfile\ShippingProfile;
use SystemConnector\TransferObject\TransferObjectInterface;
use SystemConnector\TransferObject\VatRate\VatRate;
use SystemConnector\ValueObject\Attribute\Attribute;
use SystemConnector\ValueObject\Translation\Translation;

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
     * @var VariationHelperInterface
     */
    private $variationHelper;

    /**
     * @var PropertyGroupApi
     */
    private $itemsPropertiesGroupsNamesApi;

    /**
     * @var PropertyNameApi
     */
    private $itemsPropertiesNamesApi;

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

        $this->itemsPropertiesGroupsNamesApi = new PropertyGroupApi($client);
        $this->itemsPropertiesNamesApi = new PropertyNameApi($client);
    }

    /**
     * @param array $product
     *
     * @return TransferObjectInterface[]
     */
    public function parse(array $product)
    {
        $result = [];

        if (empty($product['texts'])) {
            $this->logger->notice('the product has no text fieds and will be skipped', [
                'product id' => $product['id'],
            ]);

            return [];
        }

        $mainVariation = $this->variationHelper->getMainVariation($product['variations']);

        if (empty($mainVariation)) {
            return [];
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

        if (empty($variations)) {
            return [];
        }

        $productObject = new Product();
        $productObject->setIdentifier($identity->getObjectIdentifier());
        $productObject->setName((string) $product['texts'][0]['name1']);
        $productObject->setActive($this->getActive($variations, $mainVariation));
        $productObject->setNumber($this->variationHelper->getMainVariationNumber($mainVariation, $variations));
        $productObject->setBadges($this->getBadges($product));
        $productObject->setShopIdentifiers($this->variationHelper->getShopIdentifiers($mainVariation));
        $productObject->setManufacturerIdentifier($this->getManufacturerIdentifier($product));
        $productObject->setCategoryIdentifiers($this->getCategories($mainVariation));
        $productObject->setDefaultCategoryIdentifiers($this->getDafaultCategories($mainVariation));
        $productObject->setShippingProfileIdentifiers($this->getShippingProfiles($product));
        $productObject->setImages($this->getImages($product, $product['texts'], $result));
        $productObject->setVatRateIdentifier($this->getVatRateIdentifier($mainVariation));
        $productObject->setDescription((string) $product['texts'][0]['shortDescription']);
        $productObject->setLongDescription((string) $product['texts'][0]['description']);
        $productObject->setMetaTitle((string) $product['texts'][0]['name1']);
        $productObject->setMetaDescription((string) $product['texts'][0]['metaDescription']);
        $productObject->setMetaKeywords((string) $product['texts'][0]['keywords']);
        $productObject->setMetaRobots('INDEX, FOLLOW');
        $productObject->setLinkedProducts($this->getLinkedProducts($product));
        $productObject->setProperties($this->getProperties($mainVariation));
        $productObject->setTranslations($this->getProductTranslations($product['texts']));
        $productObject->setAvailableFrom($this->getAvailableFrom($mainVariation));
        $productObject->setAvailableTo($this->getAvailableTo($mainVariation));
        $productObject->setCreatedAt($this->getCreatedAt($mainVariation));
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
        foreach ($product['itemShippingProfiles'] as $profile) {
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
                'value' => $text['metaDescription'],
            ]);

            $translations[] = Translation::fromArray([
                'languageIdentifier' => $languageIdentifier->getObjectIdentifier(),
                'property' => 'longDescription',
                'value' => $text['description'],
            ]);

            $translations[] = Translation::fromArray([
                'languageIdentifier' => $languageIdentifier->getObjectIdentifier(),
                'property' => 'metaTitle',
                'value' => $text['name1'],
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

        foreach ($properties as $property) {
            if (!$property['property']['isSearchable']) {
                continue;
            }

            $backendName = $property['property']['backendName'];

            $values = [];
            $translations = [];

            if ($property['property']['valueType'] === 'empty' && null !== $property['property']['propertyGroupId']) {
                $propertyGroupNames = $this->itemsPropertiesGroupsNamesApi->findOne($property['property']['propertyGroupId']);

                if (empty($propertyGroupNames[0]['name'])) {
                    continue;
                }

                $backendName = $propertyGroupNames[0]['name'];

                foreach ($propertyGroupNames as $name) {
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

                $propertyNames = $this->itemsPropertiesNamesApi->findOne($property['property']['id']);
                $valueTranslations = [];

                foreach ($propertyNames as $name) {
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
                        'value' => $name['name'],
                    ]);
                }

                $values[] = Value::fromArray([
                    'value' => (string) $property['property']['backendName'],
                    'translations' => $valueTranslations,
                ]);
            } elseif ($property['property']['valueType'] === 'text') {
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

                    $translations[] = Translation::fromArray([
                        'languageIdentifier' => $languageIdentifier->getObjectIdentifier(),
                        'property' => 'name',
                        'value' => $property['property']['backendName'],
                    ]);

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
            } elseif ($property['property']['valueType'] === 'selection') {
                if (null === $property['propertySelectionId']) {
                    continue;
                }

                $valueTranslations = [];

                foreach ($property['propertySelection'] as $selection) {
                    $languageIdentifier = $this->identityService->findOneBy([
                        'adapterIdentifier' => $selection['lang'],
                        'adapterName' => PlentymarketsAdapter::NAME,
                        'objectType' => Language::TYPE,
                    ]);

                    if (null === $languageIdentifier) {
                        continue;
                    }

                    $translations[] = Translation::fromArray([
                        'languageIdentifier' => $languageIdentifier->getObjectIdentifier(),
                        'property' => 'name',
                        'value' => $property['property']['backendName'],
                    ]);

                    $valueTranslations[] = Translation::fromArray([
                        'languageIdentifier' => $languageIdentifier->getObjectIdentifier(),
                        'property' => 'value',
                        'value' => $selection['name'],
                    ]);
                }

                $values[] = Value::fromArray([
                    'value' => (string) $property['propertySelection'][0]['name'],
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
            }

            $result[] = Property::fromArray([
                'name' => $backendName,
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
     * @param array $mainVariation
     *
     * @return null|DateTimeImmutable
     */
    private function getCreatedAt(array $mainVariation)
    {
        if (!empty($mainVariation['createdAt'])) {
            return new DateTimeImmutable($mainVariation['createdAt']);
        }

        return null;
    }

    /**
     * @param Variation[] $variations
     * @param array       $mainVariation
     *
     * @return bool
     */
    private function getActive(array $variations, array $mainVariation)
    {
        $checkActiveMainVariation = json_decode($this->configService->get('check_active_main_variation'));

        if ($checkActiveMainVariation && !$mainVariation['isActive']) {
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
        $properties = [[]];

        foreach ($variations as $variation) {
            $properties[] = $variation->getProperties();
        }

        return array_merge(...$properties);
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
        $attributes[] = $this->getTechnicalDataAsAttribute($product);
        $attributes[] = $this->getAgeRestrictionAsAttribute($product);
        $attributes[] = $this->getSecondProductNameAsAttribute($product);
        $attributes[] = $this->getThirdProductNameAsAttribute($product);
        $attributes[] = $this->getItemIdAsAttribute($product);

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

    /**
     * @param array $product
     *
     * @return Attribute
     */
    private function getTechnicalDataAsAttribute(array $product)
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
                'value' => $text['technicalData'],
            ]);
        }

        $attribute = new Attribute();
        $attribute->setKey('technicalDescription');
        $attribute->setValue((string) $product['texts'][0]['technicalData']);
        $attribute->setTranslations($translations);

        return $attribute;
    }

    /**
     * @param array $product
     *
     * @return Attribute
     */
    private function getSecondProductNameAsAttribute(array $product)
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
                'value' => $text['name2'],
            ]);
        }

        $attribute = new Attribute();
        $attribute->setKey('secondProductName');
        $attribute->setValue((string) $product['texts'][0]['name2']);
        $attribute->setTranslations($translations);

        return $attribute;
    }

    /**
     * @param array $product
     *
     * @return Attribute
     */
    private function getThirdProductNameAsAttribute(array $product)
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
                'value' => $text['name3'],
            ]);
        }

        $attribute = new Attribute();
        $attribute->setKey('thirdProductName');
        $attribute->setValue((string) $product['texts'][0]['name3']);
        $attribute->setTranslations($translations);

        return $attribute;
    }

    /**
     * @param array $product
     *
     * @return Attribute
     */
    private function getAgeRestrictionAsAttribute(array $product)
    {
        $attribute = new Attribute();
        $attribute->setKey('ageRestriction');
        $attribute->setValue((string) $product['ageRestriction']);

        return $attribute;
    }

    /**
     * @param array $product
     *
     * @return Attribute
     */
    private function getArticleIdAsAttribute(array $product)
    {
        $attribute = new Attribute();
        $attribute->setKey('itemId');
        $attribute->setValue((string) $product['id']);

        return $attribute;
    }

    /**
     * @param array $product
     *
     * @return Badge[]
     */
    private function getBadges(array $product)
    {
        if ($product['storeSpecial'] === 3) {
            $badge = new Badge();
            $badge->setType(Badge::TYPE_HIGHLIGHT);

            return [$badge];
        }

        return [];
    }
}
