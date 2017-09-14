<?php

namespace PlentymarketsAdapter\ResponseParser\Product\Variation;

use DateTimeImmutable;
use PlentyConnector\Connector\ConfigService\ConfigServiceInterface;
use PlentyConnector\Connector\IdentityService\Exception\NotFoundException;
use PlentyConnector\Connector\IdentityService\IdentityServiceInterface;
use PlentyConnector\Connector\TransferObject\Language\Language;
use PlentyConnector\Connector\TransferObject\Product\Barcode\Barcode;
use PlentyConnector\Connector\TransferObject\Product\Image\Image;
use PlentyConnector\Connector\TransferObject\Product\Product;
use PlentyConnector\Connector\TransferObject\Product\Property\Property;
use PlentyConnector\Connector\TransferObject\Product\Property\Value\Value;
use PlentyConnector\Connector\TransferObject\Product\Variation\Variation;
use PlentyConnector\Connector\TransferObject\TransferObjectInterface;
use PlentyConnector\Connector\TransferObject\Unit\Unit;
use PlentyConnector\Connector\ValueObject\Attribute\Attribute;
use PlentyConnector\Connector\ValueObject\Translation\Translation;
use PlentymarketsAdapter\PlentymarketsAdapter;
use PlentymarketsAdapter\ReadApi\Availability as AvailabilityApi;
use PlentymarketsAdapter\ReadApi\Item\Attribute as AttributeApi;
use PlentymarketsAdapter\ReadApi\Item\Attribute\Value as AttributeValueApi;
use PlentymarketsAdapter\ReadApi\Item\Barcode as BarcodeApi;
use PlentymarketsAdapter\ResponseParser\Product\Image\ImageResponseParserInterface;
use PlentymarketsAdapter\ResponseParser\Product\Price\PriceResponseParserInterface;
use PlentymarketsAdapter\ResponseParser\Product\Stock\StockResponseParserInterface;

/**
 * Class VariationResponseParser
 */
class VariationResponseParser implements VariationResponseParserInterface
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
     * @var ImageResponseParserInterface
     */
    private $imageResponseParser;

    /**
     * @var StockResponseParserInterface
     */
    private $stockResponseParser;

    /**
     * @var AvailabilityApi
     */
    private $availabilitiesApi;

    /**
     * @var AttributeApi
     */
    private $itemAttributesApi;

    /**
     * @var AttributeValueApi
     */
    private $itemAttributesValuesApi;

    /**
     * @var BarcodeApi
     */
    private $itemBarcodeApi;

    /**
     * @var ConfigServiceInterface
     */
    private $config;

    /**
     * VariationResponseParser constructor.
     *
     * @param IdentityServiceInterface $identityService
     * @param PriceResponseParserInterface $priceResponseParser
     * @param ImageResponseParserInterface $imageResponseParser
     * @param StockResponseParserInterface $stockResponseParser
     * @param AvailabilityApi $availabilitiesApi
     * @param AttributeApi $itemAttributesApi
     * @param AttributeValueApi $itemAttributesValuesApi
     * @param BarcodeApi $itemBarcodeApi
     * @param ConfigServiceInterface $config
     */
    public function __construct(
        IdentityServiceInterface $identityService,
        PriceResponseParserInterface $priceResponseParser,
        ImageResponseParserInterface $imageResponseParser,
        StockResponseParserInterface $stockResponseParser,
        AvailabilityApi $availabilitiesApi,
        AttributeApi $itemAttributesApi,
        AttributeValueApi $itemAttributesValuesApi,
        BarcodeApi $itemBarcodeApi,
        ConfigServiceInterface $config
    ) {
        $this->identityService = $identityService;
        $this->priceResponseParser = $priceResponseParser;
        $this->imageResponseParser = $imageResponseParser;
        $this->stockResponseParser = $stockResponseParser;
        $this->availabilitiesApi = $availabilitiesApi;
        $this->itemAttributesApi = $itemAttributesApi;
        $this->itemAttributesValuesApi = $itemAttributesValuesApi;
        $this->itemBarcodeApi = $itemBarcodeApi;
        $this->config = $config;
    }

    /**
     * @param array $product
     *
     * @return TransferObjectInterface[]
     */
    public function parse(array $product)
    {
        $variations = $product['variations'];

        if (count($variations) > 1) {
            $variations = array_filter($variations, function (array $variation) {
                return !empty($variation['variationAttributeValues']);
            });
        }

        usort($variations, function (array $a, array $b) {
            if ((int) $a['position'] === (int) $b['position']) {
                return 0;
            }

            return ((int) $a['position'] < (int) $b['position']) ? -1 : 1;
        });

        $result = [];
        $first = true;
        foreach ($variations as $variation) {
            $identity = $this->identityService->findOneOrCreate(
                (string) $variation['id'],
                PlentymarketsAdapter::NAME,
                Variation::TYPE
            );

            $productIdentitiy = $this->identityService->findOneOrCreate(
                (string) $product['id'],
                PlentymarketsAdapter::NAME,
                Product::TYPE
            );

            $variationObject = new Variation();
            $variationObject->setIdentifier($identity->getObjectIdentifier());
            $variationObject->setProductIdentifier($productIdentitiy->getObjectIdentifier());
            $variationObject->setActive((bool) $variation['isActive']);
            $variationObject->setIsMain($first);
            $variationObject->setNumber($this->getVariationNumber($variation));
            $variationObject->setBarcodes($this->getBarcodes($variation));
            $variationObject->setPosition((int) $variation['position']);
            $variationObject->setModel((string) $variation['model']);
            $variationObject->setImages($this->getVariationImages($product['texts'], $variation, $result));
            $variationObject->setPrices($this->priceResponseParser->parse($variation));
            $variationObject->setPurchasePrice((float) $variation['purchasePrice']);
            $variationObject->setUnitIdentifier($this->getUnitIdentifier($variation));
            $variationObject->setContent((float) $variation['unit']['content']);
            $variationObject->setReferenceAmount(1.0);
            $variationObject->setMaximumOrderQuantity((float) $variation['maximumOrderQuantity']);
            $variationObject->setMinimumOrderQuantity((float) $variation['minimumOrderQuantity']);
            $variationObject->setIntervalOrderQuantity((float) $variation['intervalOrderQuantity']);
            $variationObject->setReleaseDate($this->getReleaseDate($variation));
            $variationObject->setShippingTime($this->getShippingTime($variation));
            $variationObject->setWidth((int) $variation['widthMM']);
            $variationObject->setHeight((int) $variation['heightMM']);
            $variationObject->setLength((int) $variation['lengthMM']);
            $variationObject->setWeight((int) $variation['weightNetG']);
            $variationObject->setAttributes($this->getAttributes($product));
            $variationObject->setProperties($this->getVariationProperties($variation));

            $result[$variationObject->getIdentifier()] = $variationObject;

            $possibleElements = $this->stockResponseParser->parse($variation);
            foreach ($possibleElements as $element) {
                $result[$element->getIdentifier()] = $element;
            }

            $first = false;
        }

        return $result;
    }

    /**
     * @param array $element
     *
     * @return string
     */
    private function getVariationNumber(array $element)
    {
        if ($this->config->get('variation_number_field', 'number') === 'number') {
            return (string) $element['number'];
        }

        return (string) $element['id'];
    }

    /**
     * @param array $variation
     *
     * @return null|DateTimeImmutable
     */
    private function getReleaseDate(array $variation)
    {
        if (null !== $variation['releasedAt']) {
            return new DateTimeImmutable($variation['releasedAt']);
        }

        return null;
    }

    /**
     * @param array $texts
     * @param array $variation
     * @param array $result
     *
     * @return Image[]
     */
    private function getVariationImages(array $texts, array $variation, array &$result)
    {
        $images = [];

        foreach ($variation['images'] as $entry) {
            $images[] = $this->imageResponseParser->parseImage($entry, $texts, $result);
        }

        return array_filter($images);
    }

    /**
     * @param array $variation
     *
     * @throws NotFoundException
     *
     * @return string
     */
    private function getUnitIdentifier(array $variation)
    {
        if (empty($variation['unit'])) {
            return null;
        }

        // Unit
        $unitIdentity = $this->identityService->findOneBy([
            'adapterIdentifier' => (string) $variation['unit']['unitId'],
            'adapterName' => PlentymarketsAdapter::NAME,
            'objectType' => Unit::TYPE,
        ]);

        if (null === $unitIdentity) {
            throw new NotFoundException('missing mapping for unit');
        }

        return $unitIdentity->getObjectIdentifier();
    }

    /**
     * @param array $variation
     *
     * @return int
     */
    private function getShippingTime(array $variation)
    {
        static $shippingConfigurations;

        if (null === $shippingConfigurations) {
            $shippingConfigurations = $this->availabilitiesApi->findAll();
        }

        $shippingConfiguration = array_filter($shippingConfigurations, function (array $configuration) use ($variation) {
            return $configuration['id'] === $variation['availability'];
        });

        if (empty($shippingConfiguration)) {
            return 0;
        }

        $shippingConfiguration = array_shift($shippingConfiguration);

        if (empty($shippingConfiguration['averageDays'])) {
            return 0;
        }

        return (int) $shippingConfiguration['averageDays'];
    }

    /**
     * @param array $variation
     *
     * @return Barcode[]
     */
    private function getBarcodes(array $variation)
    {
        static $barcodeMapping;

        if (null === $barcodeMapping) {
            $systemBarcodes = $this->itemBarcodeApi->findAll();

            foreach ($systemBarcodes as $systemBarcode) {
                $typeMapping = [
                    'GTIN_13' => Barcode::TYPE_GTIN13,
                    'GTIN_128' => Barcode::TYPE_GTIN128,
                    'UPC' => Barcode::TYPE_UPC,
                    'ISBN' => Barcode::TYPE_ISBN,
                ];

                if (array_key_exists($systemBarcode['type'], $typeMapping)) {
                    $barcodeMapping[$systemBarcode['id']] = $typeMapping[$systemBarcode['type']];
                }
            }

            $barcodeMapping = array_filter($barcodeMapping);
        }

        $barcodes = array_filter($variation['variationBarcodes'], function (array $barcode) use ($barcodeMapping) {
            return array_key_exists($barcode['barcodeId'], $barcodeMapping);
        });

        $barcodes = array_map(function (array $barcode) use ($barcodeMapping) {
            $barcodeObject = new Barcode();
            $barcodeObject->setType($barcodeMapping[$barcode['barcodeId']]);
            $barcodeObject->setCode($barcode['code']);

            return $barcodeObject;
        }, $barcodes);

        return $barcodes;
    }

    /**
     * @param $variation
     *
     * @return Property[]
     */
    private function getVariationProperties(array $variation)
    {
        static $attributes;

        if (null === $attributes) {
            $attributeResult = $this->itemAttributesApi->findAll();

            foreach ($attributeResult as $attribute) {
                $attributes[$attribute['id']] = $attribute;

                $values = $this->itemAttributesValuesApi->findOne($attribute['id']);

                foreach ($values as $value) {
                    $attributes[$attribute['id']]['values'][$value['id']] = $value;
                }
            }
        }

        $result = [];
        foreach ($variation['variationAttributeValues'] as $attributeValue) {
            if (!isset($attributes[$attributeValue['attributeId']]['values'][$attributeValue['valueId']]['valueNames'])) {
                continue;
            }

            $propertyNames = $attributes[$attributeValue['attributeId']]['attributeNames'];
            $valueNames = $attributes[$attributeValue['attributeId']]['values'][$attributeValue['valueId']]['valueNames'];

            $value = Value::fromArray([
                'value' => $valueNames[0]['name'],
                'translations' => $this->getVariationPropertyValueTranslations($valueNames),
            ]);

            $result[] = Property::fromArray([
                'name' => $propertyNames[0]['name'],
                'values' => [$value],
                'translations' => $this->getVariationPropertyTranslations($propertyNames),
            ]);
        }

        return $result;
    }

    /**
     * @param array $names
     *
     * @return Translation[]
     */
    private function getVariationPropertyValueTranslations(array $names)
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
    private function getVariationPropertyTranslations(array $names)
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
     * @param array $product
     *
     * @return Attribute[]
     */
    private function getAttributes(array $product)
    {
        $attributes = [];

        for ($i = 0; $i < 20; ++$i) {
            $key = 'free' . ($i + 1);

            $attributes[] = Attribute::fromArray([
                'key' => $key,
                'value' => (string) $product[$key],
            ]);
        }

        return $attributes;
    }
}
