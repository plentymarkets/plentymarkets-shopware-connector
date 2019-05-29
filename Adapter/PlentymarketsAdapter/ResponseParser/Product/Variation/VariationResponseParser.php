<?php

namespace PlentymarketsAdapter\ResponseParser\Product\Variation;

use DateTimeImmutable;
use PlentymarketsAdapter\Helper\ReferenceAmountCalculatorInterface;
use PlentymarketsAdapter\Helper\VariationHelperInterface;
use PlentymarketsAdapter\PlentymarketsAdapter;
use PlentymarketsAdapter\ResponseParser\Product\Image\ImageResponseParserInterface;
use PlentymarketsAdapter\ResponseParser\Product\Price\PriceResponseParserInterface;
use PlentymarketsAdapter\ResponseParser\Product\Stock\StockResponseParserInterface;
use SystemConnector\ConfigService\ConfigServiceInterface;
use SystemConnector\IdentityService\Exception\NotFoundException;
use SystemConnector\IdentityService\IdentityServiceInterface;
use SystemConnector\TransferObject\Language\Language;
use SystemConnector\TransferObject\Product\Barcode\Barcode;
use SystemConnector\TransferObject\Product\Image\Image;
use SystemConnector\TransferObject\Product\Product;
use SystemConnector\TransferObject\Product\Property\Property;
use SystemConnector\TransferObject\Product\Property\Value\Value;
use SystemConnector\TransferObject\Product\Variation\Variation;
use SystemConnector\TransferObject\TransferObjectInterface;
use SystemConnector\TransferObject\Unit\Unit;
use SystemConnector\ValueObject\Translation\Translation;

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
     * @var ReferenceAmountCalculatorInterface
     */
    private $referenceAmountCalculator;

    /**
     * @var VariationHelperInterface
     */
    private $variationHelper;

    /**
     * @var ConfigServiceInterface
     */
    private $configService;

    public function __construct(
        IdentityServiceInterface $identityService,
        PriceResponseParserInterface $priceResponseParser,
        ImageResponseParserInterface $imageResponseParser,
        StockResponseParserInterface $stockResponseParser,
        ReferenceAmountCalculatorInterface $referenceAmountCalculator,
        VariationHelperInterface $variationHelper,
        ConfigServiceInterface $configService
    ) {
        $this->identityService = $identityService;
        $this->priceResponseParser = $priceResponseParser;
        $this->imageResponseParser = $imageResponseParser;
        $this->stockResponseParser = $stockResponseParser;
        $this->referenceAmountCalculator = $referenceAmountCalculator;
        $this->variationHelper = $variationHelper;
        $this->configService = $configService;
    }

    /**
     * @param array $product
     *
     * @return TransferObjectInterface[]
     */
    public function parse(array $product)
    {
        $productIdentitiy = $this->identityService->findOneBy([
            'adapterIdentifier' => (string) $product['id'],
            'adapterName' => PlentymarketsAdapter::NAME,
            'objectType' => Product::TYPE,
        ]);

        if (null === $productIdentitiy) {
            return [];
        }

        $variations = $product['variations'];

        $mainVariation = $this->variationHelper->getMainVariation($variations);

        if (empty($mainVariation)) {
            return [];
        }

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

        foreach ($variations as $variation) {
            $identity = $this->identityService->findOneOrCreate(
                (string) $variation['id'],
                PlentymarketsAdapter::NAME,
                Variation::TYPE
            );

            $variationObject = new Variation();
            $variationObject->setIdentifier($identity->getObjectIdentifier());
            $variationObject->setProductIdentifier($productIdentitiy->getObjectIdentifier());
            $variationObject->setActive((bool) $variation['isActive']);
            $variationObject->setNumber($this->getVariationNumber($variation));
            $variationObject->setStockLimitation($this->getStockLimitation($variation));
            $variationObject->setBarcodes($this->getBarcodes($variation));
            $variationObject->setPosition((int) $variation['position']);
            $variationObject->setModel((string) $variation['model']);
            $variationObject->setImages($this->getVariationImages($product['texts'], $variation, $result));
            $variationObject->setPrices($this->priceResponseParser->parse($variation));
            $variationObject->setPurchasePrice((float) $variation['purchasePrice']);
            $variationObject->setUnitIdentifier($this->getUnitIdentifier($variation));
            $variationObject->setContent((float) $variation['unit']['content']);
            $variationObject->setReferenceAmount($this->referenceAmountCalculator->calculate($variation));
            $variationObject->setMaximumOrderQuantity((float) $variation['maximumOrderQuantity']);
            $variationObject->setMinimumOrderQuantity((float) $variation['minimumOrderQuantity']);
            $variationObject->setIntervalOrderQuantity((float) $variation['intervalOrderQuantity']);
            $variationObject->setReleaseDate($this->getReleaseDate($variation));
            $variationObject->setShippingTime($this->getShippingTime($product['__availabilities'], $variation));
            $variationObject->setWidth((int) $variation['widthMM']);
            $variationObject->setHeight((int) $variation['heightMM']);
            $variationObject->setLength((int) $variation['lengthMM']);
            $variationObject->setWeight($this->getVariationWeight($variation));
            $variationObject->setProperties($this->getVariationProperties($product['__attributes'], $variation));

            $stockObject = $this->stockResponseParser->parse($variation);

            if (null === $stockObject) {
                continue;
            }

            $importVariationsWithoutStock = json_decode($this->configService->get('import_variations_without_stock', true));

            if (!$importVariationsWithoutStock && empty($stockObject->getStock())) {
                continue;
            }

            $result[$variationObject->getIdentifier()] = $variationObject;
            $result[$stockObject->getIdentifier()] = $stockObject;
        }

        $variations = array_filter($result, function (TransferObjectInterface $object) {
            return $object instanceof Variation;
        });

        $mainVariationNumber = $this->variationHelper->getMainVariationNumber($variations, $mainVariation);

        foreach ($variations as &$variation) {
            if ($variation->getNumber() === $mainVariationNumber) {
                $variation->setIsMain(true);

                $checkActiveMainVariation = json_decode($this->configService->get('check_active_main_variation'));

                if ($checkActiveMainVariation && !$mainVariation['isActive']) {
                    $variation->setActive(false);
                }

                break;
            }
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
        if ($this->configService->get('variation_number_field', 'number') === 'number') {
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

        foreach ((array) $variation['images'] as $entry) {
            $images[] = $this->imageResponseParser->parseImage($entry, $texts, $result);
        }

        return array_filter($images);
    }

    /**
     * @param array $variation
     *
     * @return null|string
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
    private function getShippingTime(array $availabilities, array $variation)
    {
        static $shippingConfigurations;

        if (null === $shippingConfigurations) {
            $shippingConfigurations = $availabilities;
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
    private function getBarcodes(array $systemBarcodes, array $variation)
    {
        static $barcodeMapping;

        if (null === $barcodeMapping) {
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
     * @param array $variation
     *
     * @return Property[]
     */
    private function getVariationProperties(array $systemAttributes, array $variation)
    {
        static $attributes;

        $result = [];
        foreach ((array) $variation['variationAttributeValues'] as $attributeValue) {
            if (!isset($attributes[$attributeValue['attributeId']])) {
                $attributes[$attributeValue['attributeId']] = array_values(array_filter($systemAttributes, function (array $attribute) use ($attributeValue) {
                    return $attributeValue['attributeId'] === $attribute['id'];
                }))[0];
            }

            $values = $attributes[$attributeValue['attributeId']]['values'];

            $attributes[$attributeValue['attributeId']]['values'] = [];

            foreach ((array) $values as $value) {
                $attributes[$attributeValue['attributeId']]['values'][$value['id']] = $value;
            }

            if (!isset($attributes[$attributeValue['attributeId']]['values'][$attributeValue['valueId']]['valueNames'])) {
                continue;
            }

            $propertyNames = $attributes[$attributeValue['attributeId']]['attributeNames'];
            $propertyPosition = $attributes[$attributeValue['attributeId']]['position'];
            $valueNames = $attributes[$attributeValue['attributeId']]['values'][$attributeValue['valueId']]['valueNames'];
            $valuePosition = $attributes[$attributeValue['attributeId']]['values'][$attributeValue['valueId']]['position'];

            $value = Value::fromArray([
                'value' => $valueNames[0]['name'],
                'position' => $valuePosition,
                'translations' => $this->getVariationPropertyValueTranslations($valueNames),
            ]);

            $result[] = Property::fromArray([
                'name' => $propertyNames[0]['name'],
                'position' => $propertyPosition,
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
     * @param array $variation
     *
     * @return float
     */
    private function getVariationWeight(array $variation)
    {
        if ($variation['weightNetG'] > 0) {
            $weight = $variation['weightNetG'];
        } else {
            $weight = $variation['weightG'];
        }

        return (float) ($weight / 1000);
    }

    /**
     * @param array $variation
     *
     * @return bool
     */
    private function getStockLimitation(array $variation)
    {
        if ($variation['stockLimitation']) {
            return true;
        }

        return false;
    }
}
