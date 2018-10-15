<?php

namespace ShopwareAdapter\DataPersister\Translation;

use Psr\Log\LoggerInterface;
use Shopware_Components_Translation;
use ShopwareAdapter\DataProvider\Translation\TranslationDataProviderInterface;
use ShopwareAdapter\ShopwareAdapter;
use SystemConnector\IdentityService\IdentityServiceInterface;
use SystemConnector\TransferObject\Category\Category;
use SystemConnector\TransferObject\Language\Language;
use SystemConnector\TransferObject\Product\Product;
use SystemConnector\TransferObject\Product\Property\Property;
use SystemConnector\TransferObject\Product\Property\Value\Value;
use SystemConnector\Translation\TranslationHelperInterface;
use SystemConnector\ValueObject\Attribute\Attribute;

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

    public function __construct(
        IdentityServiceInterface $identityService,
        LoggerInterface $logger,
        TranslationDataProviderInterface $dataProvider,
        TranslationHelperInterface $translationHelper,
        Shopware_Components_Translation $shopwareTranslationManager
    ) {
        $this->identityService = $identityService;
        $this->logger = $logger;
        $this->dataProvider = $dataProvider;
        $this->translationHelper = $translationHelper;
        $this->shopwareTranslationManager = $shopwareTranslationManager;
    }

    /**
     * {@inheritdoc}
     */
    public function writeProductTranslations(Product $product)
    {
        $productIdentity = $this->identityService->findOneBy([
            'objectIdentifier' => $product->getIdentifier(),
            'objectType' => Product::TYPE,
            'adapterName' => ShopwareAdapter::NAME,
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
                'objectType' => Language::TYPE,
                'adapterName' => ShopwareAdapter::NAME,
            ]);

            if (null === $languageIdentity) {
                $this->logger->notice('language not mapped - ' . $languageIdentifier);

                continue;
            }

            $translation = [
                'name' => $translatedProduct->getName(),
                'description' => $translatedProduct->getDescription(),
                'descriptionLong' => $translatedProduct->getLongDescription(),
                'metaTitle' => $translatedProduct->getMetaTitle(),
                'metaDescription' => $translatedProduct->getMetaDescription(),
                'keywords' => $translatedProduct->getMetaKeywords(),
            ];

            foreach ($product->getAttributes() as $attribute) {
                /**
                 * @var Attribute $translatedAttribute
                 */
                $translatedAttribute = $this->translationHelper->translate($languageIdentifier, $attribute);

                $key = '__attribute_plenty_connector' . ucfirst($attribute->getKey());
                $attribute_key = strtolower(preg_replace('/[A-Z]/', '_\\0', lcfirst($key)));
                $translation[$attribute_key] = $translatedAttribute->getValue();
            }

            $this->writeTranslations('article', (int) $productIdentity->getAdapterIdentifier(), $translation);
        }

        foreach ($product->getProperties() as $property) {
            $this->writeGroupTranslations($property, 'propertyoption');

            foreach ($property->getValues() as $value) {
                $this->writeValueTranslations($value, 'propertyvalue');
            }
        }

        foreach ($product->getVariantConfiguration() as $variantConfiguration) {
            $this->writeGroupTranslations($variantConfiguration, 'configuratorgroup');

            foreach ($variantConfiguration->getValues() as $value) {
                $this->writeValueTranslations($value, 'configuratoroption');
            }
        }
    }

    /**
     * @param Property $property
     * @param string   $type
     */
    private function writeGroupTranslations(Property $property, $type)
    {
        $groupModel = null;

        if ($type === 'propertyoption') {
            $groupModel = $this->dataProvider->getPropertyOptionByName($property);
        } elseif ($type === 'configuratorgroup') {
            $groupModel = $this->dataProvider->getConfigurationGroupByName($property);
        }

        if (null === $groupModel) {
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
                'objectType' => Language::TYPE,
                'adapterName' => ShopwareAdapter::NAME,
            ]);

            if (null === $languageIdentity) {
                $this->logger->notice('language not mapped - ' . $languageIdentifier);

                continue;
            }

            if ($type === 'propertyoption') {
                $translation = [
                    'languageIdentity' => $languageIdentity,
                    'optionName' => $translatedProperty->getName(),
                ];
            } elseif ($type === 'configuratorgroup') {
                $translation = [
                    'languageIdentity' => $languageIdentity,
                    'name' => $translatedProperty->getName(),
                ];
            }

            if (empty($translation)) {
                continue;
            }

            $this->writeTranslations($type, $groupModel->getId(), $translation);
        }
    }

    /**
     * @param Value  $value
     * @param string $type
     */
    private function writeValueTranslations(Value $value, $type)
    {
        $valueModel = null;

        if ($type === 'propertyvalue') {
            $valueModel = $this->dataProvider->getPropertyValueByValue($value);
        } elseif ($type === 'configuratoroption') {
            $valueModel = $this->dataProvider->getConfigurationOptionByName($value);
        }

        if (null === $valueModel) {
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
                'objectType' => Language::TYPE,
                'adapterName' => ShopwareAdapter::NAME,
            ]);

            if (null === $languageIdentity) {
                $this->logger->notice('language not mapped - ' . $languageIdentifier);

                continue;
            }

            if ($type === 'propertyvalue') {
                $translation = [
                    'languageIdentity' => $languageIdentity,
                    'optionValue' => $translatedPropertyValue->getValue(),
                ];
            } elseif ($type === 'configuratoroption') {
                $translation = [
                    'languageIdentity' => $languageIdentity,
                    'name' => $translatedPropertyValue->getValue(),
                ];
            }

            if (empty($translation)) {
                continue;
            }

            $this->writeTranslations($type, $valueModel->getId(), $translation);
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

    /**
     * @param Category $category
     */
    public function writeCategoryTranslations(Category $category)
    {
        $categoryIdentity = $this->identityService->findOneBy([
            'objectIdentifier' => $category->getIdentifier(),
            'objectType' => Category::TYPE,
            'adapterName' => ShopwareAdapter::NAME,
        ]);

        if (null === $categoryIdentity) {
            return;
        }

        foreach ($this->translationHelper->getLanguageIdentifiers($category) as $languageIdentifier) {
            /**
             * @var Category $translatedCategory
             */
            $translatedCategory = $this->translationHelper->translate($languageIdentifier, $category);

            $languageIdentity = $this->identityService->findOneBy([
                'objectIdentifier' => $languageIdentifier,
                'objectType' => Language::TYPE,
                'adapterName' => ShopwareAdapter::NAME,
            ]);

            if (null === $languageIdentity) {
                $this->logger->notice('language not mapped - ' . $languageIdentifier);

                continue;
            }

            $translation = [
                'name' => $translatedCategory->getName(),
                'metaTitle' => $translatedCategory->getMetaTitle(),
                'metaKeywords' => $translatedCategory->getMetaKeywords(),
                'metaDescription' => $translatedCategory->getMetaDescription(),
                'cmsHeadline' => $translatedCategory->getDescription(),
                'cmsText' => $translatedCategory->getLongDescription(),
            ];

            foreach ($category->getAttributes() as $attribute) {
                /**
                 * @var Attribute $translatedAttribute
                 */
                $translatedAttribute = $this->translationHelper->translate($languageIdentifier, $attribute);

                $key = '__attribute_plenty_connector' . ucfirst($attribute->getKey());
                $attribute_key = strtolower(preg_replace('/[A-Z]/', '_\\0', lcfirst($key)));
                $translation[$attribute_key] = $translatedAttribute->getValue();
            }

            $this->writeTranslations('category', (int) $categoryIdentity->getAdapterIdentifier(), $translation);
        }
    }
}
