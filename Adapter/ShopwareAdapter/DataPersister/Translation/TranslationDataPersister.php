<?php

namespace ShopwareAdapter\DataPersister\Translation;

use PlentyConnector\Connector\IdentityService\IdentityServiceInterface;
use PlentyConnector\Connector\TransferObject\Language\Language;
use PlentyConnector\Connector\TransferObject\Product\Product;
use PlentyConnector\Connector\TransferObject\Product\Property\Property;
use PlentyConnector\Connector\TransferObject\Product\Property\Value\Value;
use PlentyConnector\Connector\Translation\TranslationHelperInterface;
use PlentyConnector\Connector\ValueObject\Attribute\Attribute;
use Psr\Log\LoggerInterface;
use Shopware_Components_Translation;
use ShopwareAdapter\DataProvider\Translation\TranslationDataProviderInterface;
use ShopwareAdapter\ShopwareAdapter;

/**
 * Class TranslationDataPersister
 */
class TranslationDataPersister implements TranslationDataPersisterInterface
{
    /**
     * @var IdentityServiceInterface
     */
    private $identityService;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var TranslationDataProviderInterface
     */
    private $dataProvider;

    /**
     * @var TranslationHelperInterface
     */
    private $translationHelper;

    /**
     * @var Shopware_Components_Translation
     */
    private $shopwareTranslationManager;

    /**
     * TranslationHelper constructor.
     *
     * @param IdentityServiceInterface         $identityService
     * @param LoggerInterface                  $logger
     * @param TranslationDataProviderInterface $dataProvider
     * @param TranslationHelperInterface       $translationHelper
     * @param Shopware_Components_Translation  $shopwareTranslationManager
     */
    public function __construct(
        IdentityServiceInterface $identityService,
        LoggerInterface $logger,
        TranslationDataProviderInterface $dataProvider,
        TranslationHelperInterface $translationHelper,
        Shopware_Components_Translation $shopwareTranslationManager
    ) {
        $this->identityService            = $identityService;
        $this->logger                     = $logger;
        $this->dataProvider               = $dataProvider;
        $this->translationHelper          = $translationHelper;
        $this->shopwareTranslationManager = $shopwareTranslationManager;
    }

    /**
     * {@inheritdoc}
     */
    public function writeProductTranslations(Product $product)
    {
        $productIdentity = $this->identityService->findOneBy([
            'objectIdentifier' => $product->getIdentifier(),
            'objectType'       => Product::TYPE,
            'adapterName'      => ShopwareAdapter::NAME,
        ]);

        if (null === $productIdentity) {
            return;
        }

        foreach ($this->translationHelper->getLanguageIdentifiers($product) as $languageIdentifier) {
            /**
             * @var Product $translatedProduct
             */
            $translatedProduct = $this->translationHelper->translate($languageIdentifier, $product);

            $languageIdentity = $this->identityService->findOneBy([
                'objectIdentifier' => $languageIdentifier,
                'objectType'       => Language::TYPE,
                'adapterName'      => ShopwareAdapter::NAME,
            ]);

            if (null === $languageIdentity) {
                $this->logger->notice('language not mapped - ' . $languageIdentifier);

                continue;
            }

            $translation = [
                'languageIdentity' => $languageIdentity,
                'name'             => $translatedProduct->getName(),
                'description'      => $translatedProduct->getDescription(),
                'descriptionLong'  => $translatedProduct->getLongDescription(),
                'keywords'         => $translatedProduct->getMetaKeywords(),
            ];

            foreach ($product->getAttributes() as $attribute) {
                /**
                 * @var Attribute $translatedAttribute
                 */
                $translatedAttribute = $this->translationHelper->translate($languageIdentifier, $attribute);

                $key               = 'plentyConnector' . ucfirst($attribute->getKey());
                $translation[$key] = $translatedAttribute->getValue();
            }

            $this->writeTranslations('article', $productIdentity->getAdapterIdentifier(), $translation);
        }

        foreach ($product->getProperties() as $property) {
            $this->writePropertyGroupTranslations($property);

            foreach ($property->getValues() as $value) {
                $this->writePropertyValueTranslations($value);
            }
        }
    }

    /**
     * @param Value $value
     */
    private function writePropertyValueTranslations(Value $value)
    {
        $propertyValueModel = $this->dataProvider->getPropertyValueByValue($value);

        if (null === $propertyValueModel) {
            $this->logger->notice('property value not found - ' . $value->getValue());

            return;
        }

        foreach ($this->translationHelper->getLanguageIdentifiers($value) as $languageIdentifier) {
            /**
             * @var Value $translatedPropertyValue
             */
            $translatedPropertyValue = $this->translationHelper->translate($languageIdentifier, $value);

            $languageIdentity = $this->identityService->findOneBy([
                'objectIdentifier' => $languageIdentifier,
                'objectType'       => Language::TYPE,
                'adapterName'      => ShopwareAdapter::NAME,
            ]);

            if (null === $languageIdentity) {
                $this->logger->notice('language not mapped - ' . $languageIdentifier);

                continue;
            }

            $translation = [
                'languageIdentity' => $languageIdentity,
                'optionValue'      => $translatedPropertyValue->getValue(),
            ];

            $this->writeTranslations('propertyvalue', (int) $propertyValueModel->getId(), $translation);
        }
    }

    /**
     * @param Property $property
     */
    private function writePropertyGroupTranslations(Property $property)
    {
        $propertyOptionModel = $this->dataProvider->getPropertyOptionByName($property);

        if (null === $propertyOptionModel) {
            $this->logger->notice('property option not found - ' . $property->getName());

            return;
        }

        foreach ($this->translationHelper->getLanguageIdentifiers($property) as $languageIdentifier) {
            /**
             * @var Property $translatedProperty
             */
            $translatedProperty = $this->translationHelper->translate($languageIdentifier, $property);

            $languageIdentity = $this->identityService->findOneBy([
                'objectIdentifier' => $languageIdentifier,
                'objectType'       => Language::TYPE,
                'adapterName'      => ShopwareAdapter::NAME,
            ]);

            if (null === $languageIdentity) {
                $this->logger->notice('language not mapped - ' . $languageIdentifier);

                continue;
            }

            $translation = [
                'languageIdentity' => $languageIdentity,
                'optionName'       => $translatedProperty->getName(),
            ];

            $this->writeTranslations('propertyoption', (int) $propertyOptionModel->getId(), $translation);
        }
    }

    /**
     * @param string $type
     * @param int    $primaryKey
     * @param array  $translation
     */
    private function writeTranslations($type, $primaryKey, array $translation)
    {
        $shops = $this->dataProvider->getShopsByLocaleIdentitiy($translation['languageIdentity']);

        foreach ($shops as $shop) {
            $this->shopwareTranslationManager->write($shop->getId(), $type, $primaryKey, $translation);
        }
    }
}
